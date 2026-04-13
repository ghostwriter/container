<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\DontCloneContainerException;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Exception\InvalidArgumentException;
use Ghostwriter\Container\Exception\InvokableClassMustBeCallableException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Ghostwriter\Container\Interface\Service\Provider\ComposerDefinitionProviderInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Provider\ComposerDefinitionProvider;
use Override;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Tests\Unit\ContainerTest;
use Throwable;

use function array_key_exists;
use function array_key_last;
use function array_keys;
use function class_exists;
use function explode;
use function function_exists;
use function implode;
use function interface_exists;
use function is_a;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function mb_trim;
use function sprintf;
use function str_contains;

/**
 * @see ContainerTest
 */
final class Container implements ContainerInterface
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $aliases = [];

    /** @var array<non-empty-string, array<non-empty-string, non-empty-string>> */
    private array $bindings = [];

    /** @var array<non-empty-string, list<ReflectionParameter>> */
    private array $callableParameterCache = [];

    /** @var array<class-string, list<ReflectionParameter>> */
    private array $constructorParameterCache = [];

    /** @var array<class-string<DefinitionInterface>, true> */
    private array $definitions = [];

    /** @var array<non-empty-string, true> */
    private array $dependencies = [];

    /** @var array<non-empty-string, array<non-empty-string, true>> */
    private array $extensions = [];

    /** @var array<non-empty-string, class-string<FactoryInterface<object>>> */
    private array $factories = [];

    /** @var array<non-empty-string, object> */
    private array $instances = [];

    /** @var array<class-string, ReflectionClass<object>> */
    private array $reflectionClasses = [];

    /** @var array<callable-string, ReflectionFunction> */
    private array $reflectionFunctions = [];

    private function __construct()
    {
        // Singleton
        $this->reset();
    }

    public static function getInstance(): self
    {
        static $instance;

        if ($instance instanceof self) {
            return $instance;
        }

        return $instance = new self();
    }

    /**
     * @throws DontCloneContainerException
     *
     * @return never
     */
    public function __clone()
    {
        throw new DontCloneContainerException();
    }

    /** @throws DontSerializeContainerException */
    public function __serialize(): array
    {
        throw new DontSerializeContainerException();
    }

    /**
     * @template TMixed
     *
     * @param list<TMixed> $_
     *
     * @throws DontUnserializeContainerException
     */
    public function __unserialize(array $_): never
    {
        throw new DontUnserializeContainerException();
    }

    /**
     * @template TService of object
     * @template TAlias of object
     *
     * @param class-string<TAlias>   $alias
     * @param class-string<TService> $service
     *
     * @throws AliasNameAndServiceNameCannotBeTheSameException
     */
    #[Override]
    public function alias(string $alias, string $service): void
    {
        $this->assertValidAlias($alias);
        $this->assertValidService($service);
        $this->assertAliasIsNotService($alias, $service);
        $this->aliases[$alias] = $service;
    }

    /**
     * @template TConcrete of object
     * @template TAbstract of object
     * @template TImplementation of object
     *
     * @param class-string<TConcrete>       $concrete
     * @param class-string<TAbstract>       $abstract
     * @param class-string<TImplementation> $implementation
     */
    #[Override]
    public function bind(string $concrete, string $abstract, string $implementation): void
    {
        $this->assertValidService($concrete);
        $this->assertValidService($abstract);
        $this->assertValidService($implementation);
        $this->bindings[$concrete][$abstract] = $implementation;
    }

    /**
     * @template TService of object
     * @template TArgument
     *
     * @param class-string<TService> $service
     * @param list<TArgument>        $arguments
     *
     * @throws CircularDependencyException
     * @throws ClassNotInstantiableException
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws Throwable
     *
     * @return TService
     */
    #[Override]
    public function build(string $service, array $arguments = []): object
    {
        /** @var class-string<TService> $service */
        $service = $this->normalizeService($service);

        /** @var TService */
        return $this->createViaInstantiation($service, $arguments);
    }

    /**
     * @template TService of object
     * @template TArgument
     * @template TResult
     *
     * @param list<TArgument>                                                                                                                $arguments
     * @param array{0:(class-string<TService>|TService),1:'__invoke'|string}|callable|callable-string|Closure(TArgument...):TResult|TService $callback
     *
     * @throws Throwable
     *
     * @return TResult
     */
    #[Override]
    public function call(callable|string $callable, array $arguments = []): mixed
    {
        $callable = $this->normalizeCallable($callable);

        $reflectionParameters = $this->callableParameters($callable);

        $parameters = $this->resolveParameters($arguments, $reflectionParameters);

        /** @var TResult */
        return $callable(...$parameters);
    }

    /**
     * @param class-string<DefinitionInterface> $definition
     *
     * @throws Throwable
     */
    #[Override]
    public function define(string $definition): void
    {
        if (array_key_exists($definition, $this->definitions)) {
            return;
        }

        if (! is_a($definition, DefinitionInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Service definition "%s" must implement %s.',
                    mb_trim($definition),
                    DefinitionInterface::class
                )
            );
        }

        $this->definitions[$definition] = true;

        $this->call($definition);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>                     $service
     * @param class-string<ExtensionInterface<TService>> $extension
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     */
    #[Override]
    public function extend(string $service, string $extension): void
    {
        $this->assertValidExtension($service, $extension);

        $this->extensions[$service][$extension] = true;
    }

    /**
     * Provide a FactoryInterface for a service.
     *
     * @template TService of object
     *
     * @param class-string<TService>                   $service
     * @param class-string<FactoryInterface<TService>> $factory
     *
     * @throws Throwable
     */
    #[Override]
    public function factory(string $service, string $factory): void
    {
        $this->assertValidFactory($service, $factory);
        $this->factories[$service] = $factory;
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws ContainerNotFoundExceptionInterface
     *
     * @return TService
     */
    #[Override]
    public function get(string $service): object
    {
        /** @var class-string<TService> $service */
        $service = $this->normalizeService($service);

        if ($this->hasService($service)) {
            /** @var TService */
            return $this->getService($service);
        }

        /** @var TService */
        return $this->instances[$service] = $this->createService($service);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @return TService
     */
    public function getService(string $service): object
    {
        return $this->instances[$service];
    }

    #[Override]
    public function has(string $service): bool
    {
        $service = $this->normalizeService($service);

        return match (true) {
            $this->serviceExists($service) => true,
            default => $this->canCreateService($service)
        };
    }

    /** @throws Throwable */
    #[Override]
    public function reset(): void
    {
        $this->aliases = [
            ContainerInterface::class => self::class,
            BuilderInterface::class => self::class,
            ComposerDefinitionProviderInterface::class => ComposerDefinitionProvider::class,
        ];

        $this->bindings = [];
        $this->callableParameterCache = [];
        $this->constructorParameterCache = [];
        $this->definitions = [];
        $this->dependencies = [];
        $this->extensions = [];
        $this->factories = [];
        $this->instances = [];
        $this->reflectionClasses = [];
        $this->reflectionFunctions = [];

        $this->define(ComposerExtraDefinition::class);

        $composerDefinitionProvider = $this->get(ComposerDefinitionProviderInterface::class);
        $composerDefinitionProvider->register($this);
        $composerDefinitionProvider->boot($this);
    }

    #[Override]
    public function set(string $service, object $instance): void
    {
        $this->assertValidService($service);
        $this->instances[$service] = $instance;
    }

    #[Override]
    public function unset(string $service): void
    {
        $this->removeRelatedAliases($service);
        $this->removeServiceState($service);
    }

    private function applicableExtensionsFor(object $instance): iterable
    {
        foreach (array_keys($this->extensions) as $serviceClass) {
            if (! $instance instanceof $serviceClass) {
                continue;
            }

            yield from array_keys($this->extensions[$serviceClass]);
        }
    }

    private function assertAliasIsNotService(string $alias, string $service): void
    {
        if ($alias === $service) {
            throw new AliasNameAndServiceNameCannotBeTheSameException($alias);
        }
    }

    private function assertCallableInstance(object $instance, string $service): callable
    {
        if (! is_callable($instance)) {
            throw new InvokableClassMustBeCallableException($service);
        }

        return $instance;
    }

    private function assertValidAlias(string $class): void
    {
        if ($this->classOrInterfaceExists($class)) {
            return;
        }

        throw new ServiceNotFoundException(sprintf('Alias "%s" is not a valid class or interface.', $class));
    }

    private function assertValidExtension(string $service, string $extension): void
    {
        $this->assertValidService($service);

        if (! is_a($extension, ExtensionInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Service extension "%s" for service "%s" must implement %s.',
                    mb_trim($extension),
                    mb_trim($service),
                    ExtensionInterface::class
                )
            );
        }
    }

    private function assertValidFactory(string $service, string $factory): void
    {
        $this->assertValidService($service);
        if (! is_a($factory, FactoryInterface::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Factory "%s" for service "%s" must implement %s.',
                mb_trim($factory),
                mb_trim($service),
                FactoryInterface::class
            ));
        }
    }

    private function assertValidService(string $class): void
    {
        if ($this->classOrInterfaceExists($class)) {
            return;
        }

        throw new ServiceNotFoundException(sprintf('Service "%s" is not a valid class or interface.', $class));
    }

    /**
     * @param array{0:class-string|object, 1:string} $callable
     *
     * @throws Throwable
     *
     * @return list<ReflectionParameter>
     */
    private function callableArrayParameters(array $callable): array
    {
        $service = $callable[0];

        if (is_object($service)) {
            $service = $service::class;
        }

        $method = $callable[1] ?? '__invoke';

        //        return $this->reflectedClass($service)
        //            ->getMethod($method)
        //            ->getParameters()
        return $this->reflectedClass($service)
            ->getMethod($method)
            ->getParameters();
    }

    private function callableCacheKey(callable|string $callable): ?string
    {
        return match (true) {
            $callable instanceof Closure => null,
            is_array($callable) => sprintf(
                '%s::%s',
                is_object($callable[0]) ? $callable[0]::class : $callable[0],
                $callable[1] ?? '__invoke'
            ),
            is_string($callable) => $callable,
            default => $callable::class . '::__invoke',
        };
    }

    /**
     * @throws Throwable
     *
     * @return list<ReflectionParameter>
     */
    private function callableClosureParameters(Closure $callable): array
    {
        return $this->reflectedFunction($callable)->getParameters();
    }

    /**
     * @param callable|string $callable
     *
     * @throws Throwable
     *
     * @return list<ReflectionParameter>
     */
    private function callableParameters(callable|string $callable): array
    {
        $cacheKey = $this->callableCacheKey($callable);

        if (null !== $cacheKey && array_key_exists($cacheKey, $this->callableParameterCache)) {
            return $this->callableParameterCache[$cacheKey];
        }

        $parameters = match (true) {
            $callable instanceof Closure => $this->callableClosureParameters($callable),
            is_array($callable) => $this->callableArrayParameters($callable),
            is_string($callable) => $this->callableStringParameters($callable),
            default => $this->callableArrayParameters([$callable, '__invoke']),
        };

        if (null !== $cacheKey) {
            $this->callableParameterCache[$cacheKey] = $parameters;
        }

        return $parameters;
    }

    /**
     * @param class-string|string $callable
     *
     * @throws Throwable
     *
     * @return list<ReflectionParameter>
     */
    private function callableStringParameters(string $callable): array
    {
        return match (true) {
            function_exists($callable) => $this->reflectedFunction($callable)->getParameters(),
            class_exists($callable) => $this->callableArrayParameters([$callable, '__invoke']),
            str_contains($callable, '::') => $this->callableArrayParameters($this->parseStaticMethod($callable)),
            default => throw new InvokableClassMustBeCallableException($callable),
        };
    }

    /** @param array<non-empty-string, true> $stack */
    private function canCreateService(string $service, array $stack = []): bool
    {
        if (self::class === $service) {
            return true;
        }

        if ($this->hasService($service) || $this->hasFactory($service)) {
            return true;
        }

        if (! class_exists($service) || array_key_exists($service, $stack)) {
            return false;
        }

        $reflectionClass = $this->reflectedClass($service);

        if (! $reflectionClass->isInstantiable()) {
            return false;
        }

        $stack[$service] = true;

        foreach ($this->constructorParameters($reflectionClass) as $reflectionParameter) {
            if ($this->parameterCanBeResolved($reflectionParameter, $service, $stack)) {
                continue;
            }

            return false;
        }

        return true;
    }

    private function classOrInterfaceExists(string $service): bool
    {
        return class_exists($service) || interface_exists($service);
    }

    /**
     * @template TService of object
     *
     * @param ReflectionClass<TService> $reflectionClass
     *
     * @return list<ReflectionParameter>
     */
    private function constructorParameters(ReflectionClass $reflectionClass): array
    {
        $service = $reflectionClass->getName();

        if (array_key_exists($service, $this->constructorParameterCache)) {
            return $this->constructorParameterCache[$service];
        }

        $constructor = $reflectionClass->getConstructor();

        $parameters = $constructor instanceof ReflectionMethod ? $constructor->getParameters() : [];

        return $this->constructorParameterCache[$service] = $parameters;
    }

    /** @param array<string, mixed> $arguments */
    private function consumeNamedArgument(string $name, array &$arguments): mixed
    {
        $argument = $arguments[$name];

        unset($arguments[$name]);

        return $argument;
    }

    /** @param array<int, mixed> $arguments */
    private function consumePositionalArgument(int $position, array &$arguments): mixed
    {
        $argument = $arguments[$position];

        unset($arguments[$position]);

        return $argument;
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws Throwable
     *
     * @return TService
     */
    private function createService(string $service): object
    {
        if ($this->hasFactory($service)) {
            /** @var TService */
            return $this->createViaFactory($service);
        }

        if (class_exists($service)) {
            /** @var TService */
            return $this->createViaInstantiation($service);
        }

        throw $this->serviceNotFound($service);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws Throwable
     *
     * @return TService
     */
    private function createViaFactory(string $service): object
    {
        /** @var TService $instance */
        $instance = $this->call($this->factories[$service]);

        return $this->decorate($service, $instance);
    }

    /**
     * @template TService of object
     * @template TArgument
     *
     * @param class-string<TService> $service
     * @param list<TArgument>        $arguments
     *
     * @throws Throwable
     *
     * @return TService
     */
    private function createViaInstantiation(string $service, array $arguments = []): object
    {
        if (self::class === $service) {
            return $this;
        }

        $this->guardCircularDependency($service);

        $this->dependencies[$service] = true;

        try {
            return $this->decorate($service, $this->instantiate($service, $arguments));
        } finally {
            unset($this->dependencies[$service]);
        }
    }

    private function currentConcrete(): ?string
    {
        $concrete = array_key_last($this->dependencies);

        return is_string($concrete) ? $concrete : null;
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     * @param TService               $instance
     *
     * @throws Throwable
     *
     * @return TService
     */
    private function decorate(string $service, object $instance): object
    {
        $this->instances[$service] = $instance;

        try {
            foreach ($this->applicableExtensionsFor($instance) as $extension) {
                /** @var TService $instance */
                $this->call($extension, [
                    'service' => $instance,
                ]);
            }

            return $instance;
        } finally {
            unset($this->instances[$service]);
        }
    }

    private function dependencyChainFor(string $service): string
    {
        return sprintf('Class: %s -> %s', implode(' -> ', array_keys($this->dependencies)), $service);
    }

    private function guardCircularDependency(string $service): void
    {
        if (array_key_exists($service, $this->dependencies)) {
            throw new CircularDependencyException($this->dependencyChainFor($service));
        }
    }

    private function hasFactory(string $service): bool
    {
        return array_key_exists($service, $this->factories);
    }

    private function hasNamedArgument(ReflectionParameter $reflectionParameter, array $arguments): bool
    {
        return array_key_exists($reflectionParameter->getName(), $arguments);
    }

    private function hasPositionalArgument(ReflectionParameter $reflectionParameter, array $arguments): bool
    {
        return array_key_exists($reflectionParameter->getPosition(), $arguments);
    }

    private function hasService(string $service): bool
    {
        return array_key_exists($service, $this->instances);
    }

    /**
     * @template TService of object
     * @template TArgument
     *
     * @param class-string<TService> $service
     * @param list<TArgument>        $arguments
     *
     * @throws Throwable
     *
     * @return TService
     */
    private function instantiate(string $service, array $arguments = []): object
    {
        $reflectionClass = $this->reflectedClass($service);

        if (! $reflectionClass->isInstantiable()) {
            throw new ClassNotInstantiableException($service);
        }

        $parameters = $this->resolveParameters($arguments, [], $reflectionClass);

        try {
            $instance = $reflectionClass->newInstance(...$parameters);
            //        } catch (Throwable $throwable) {
        } catch (ContainerExceptionInterface $throwable) {
            throw $throwable;
        } catch (Throwable $throwable) {
            throw new InstantiatorException($throwable->getMessage(), 127, $throwable);
        }

        return $instance;
    }

    private function normalizeCallable(callable|string $callable): callable|string
    {
        if (! is_string($callable) || ! $this->classOrInterfaceExists($callable)) {
            return $callable;
        }

        return $this->assertCallableInstance($this->get($callable), $callable);
    }

    private function normalizeService(string $service): string
    {
        $this->assertValidService($service);

        return $this->resolveContextualBindingForConcrete($this->resolveAlias($service), $this->currentConcrete());
    }

    private function parameterCanBeResolved(
        ReflectionParameter $reflectionParameter,
        string $concrete,
        array $stack
    ): bool {
        if ($reflectionParameter->isOptional() || $reflectionParameter->isDefaultValueAvailable()) {
            return true;
        }

        $reflectionType = $reflectionParameter->getType();

        if (! $reflectionType instanceof ReflectionNamedType) {
            return false;
        }

        if ($reflectionType->isBuiltin()) {
            return false;
        }

        $service = $this->resolveContextualBindingForConcrete(
            $this->resolveAlias($reflectionType->getName()),
            $concrete
        );

        return $this->canCreateService($service, $stack);
    }

    /**
     * @param class-string $callback
     *
     * @return array{0:class-string, 1:non-empty-string}
     */
    private function parseStaticMethod(string $callback): array
    {
        /** @var array{0:class-string,1:non-empty-string} */
        return explode('::', $callback, 2);
    }

    private function reflectedClass(object|string $objectOrClass): ReflectionClass
    {
        $class = is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;

        if (! array_key_exists($class, $this->reflectionClasses)) {
            /** @var ReflectionClass<object> */
            $this->reflectionClasses[$class] = new ReflectionClass($objectOrClass);
        }

        return $this->reflectionClasses[$class];
    }

    private function reflectedFunction(Closure|string $function): ReflectionFunction
    {
        if ($function instanceof Closure) {
            return new ReflectionFunction($function);
        }

        if (! array_key_exists($function, $this->reflectionFunctions)) {
            $this->reflectionFunctions[$function] = new ReflectionFunction($function);
        }

        return $this->reflectionFunctions[$function];
    }

    private function removeRelatedAliases(string $service): void
    {
        foreach (array_keys($this->aliases) as $alias) {
            if ($alias === $service || $this->aliases[$alias] === $service) {
                unset($this->aliases[$alias]);
            }
        }
    }

    private function removeServiceState(string $service): void
    {
        unset(
            $this->instances[$service],
            $this->extensions[$service],
            $this->factories[$service],
        );
    }

    private function resolveAlias(string $service): string
    {
        while (array_key_exists($service, $this->aliases)) {
            $service = $this->aliases[$service];
        }

        return $service;
    }

    private function resolveContextualBindingForConcrete(string $service, ?string $concrete): string
    {
        if (null === $concrete) {
            return $service;
        }

        return $this->bindings[$concrete][$service] ?? $service;
    }

    private function resolveParameterValue(ReflectionParameter $reflectionParameter): mixed
    {
        $reflectionType = $reflectionParameter->getType();

        if (! $reflectionType instanceof ReflectionNamedType || $reflectionType->isBuiltin()) {
            return $this->resolveUntypedParameter($reflectionParameter);
        }

        return $this->resolveTypedParameter($reflectionParameter, $reflectionType);
    }

    /**
     * @template TArgument
     * @template TService of object
     *
     * @param list<TArgument>           $arguments
     * @param list<ReflectionParameter> $reflectionParameters
     *
     * @throws Throwable
     *
     * @return list<TArgument|TService>
     */
    private function resolveParameters(
        array $arguments = [],
        array $reflectionParameters = [],
        ?ReflectionClass $reflectionClass = null
    ): array {
        $reflectionParameters = $reflectionClass instanceof ReflectionClass
            ? $this->constructorParameters($reflectionClass)
            : $reflectionParameters;

        /** @var list<TArgument|TService> $parameters */
        $parameters = [];

        foreach ($reflectionParameters as $reflectionParameter) {
            $parameterPosition = $reflectionParameter->getPosition();
            $parameterName = $reflectionParameter->getName();

            if (array_key_exists($parameterName, $arguments)) {
                /** @var TArgument $parameters */
                $parameters[$parameterPosition] = $this->consumeNamedArgument($parameterName, $arguments);

                continue;
            }

            if ($this->hasPositionalArgument($reflectionParameter, $arguments)) {
                /** @var TArgument $parameters */
                $parameters[$parameterPosition] = $this->consumePositionalArgument($parameterPosition, $arguments);

                continue;
            }

            if ($reflectionParameter->isOptional()) {
                continue;
            }

            $parameters[$parameterPosition] = $this->resolveParameterValue($reflectionParameter);
        }

        return $parameters;
    }

    private function resolveTypedParameter(
        ReflectionParameter $reflectionParameter,
        ReflectionNamedType $reflectionNamedType
    ): mixed {
        /** @var class-string $service */
        $service = $reflectionNamedType->getName();

        if ($this->hasService($service)) {
            return $this->getService($service);
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        return $this->get($service);
    }

    private function resolveUntypedParameter(ReflectionParameter $reflectionParameter): mixed
    {
        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        $this->throwUnresolvableParameter($reflectionParameter);
    }

    private function serviceExists(string $service): bool
    {
        return match (true) {
            $this->hasService($service) => true,
            default => $this->hasFactory($service)
        };
    }

    private function serviceNotFound(string $service): ServiceNotFoundException
    {
        return new ServiceNotFoundException(
            [] !== $this->dependencies
                ? sprintf(
                    'Service "%s" not found, required by "%s".',
                    $service,
                    array_key_last($this->dependencies)
                )
                : $service
        );
    }

    /**
     * @throws UnresolvableParameterException
     *
     * @return never
     */
    private function throwUnresolvableParameter(ReflectionParameter $reflectionParameter): never
    {
        $name = $reflectionParameter->getDeclaringFunction()->getName();
        $isFunction = function_exists($name);

        throw new UnresolvableParameterException(sprintf(
            'Unresolvable %s parameter "$%s" in "%s%s()"; does not have a default value.',
            $isFunction ? 'function' : 'class',
            $reflectionParameter->getName(),
            $isFunction ? $name : $reflectionParameter->getDeclaringClass()?->getName() ?? '',
            $isFunction ? '' : '::' . $name
        ));
    }
}
