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
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Override;
use ReflectionClass;
use ReflectionException;
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
use function enum_exists;
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

    /** @var array<class-string<DefinitionInterface>, true> */
    private array $definitions = [];

    /** @var list<non-empty-string> */
    private array $dependencies = [];

    /** @var array<non-empty-string, array<non-empty-string, true>> */
    private array $extensions = [];

    /** @var array<non-empty-string, class-string<FactoryInterface<object>>> */
    private array $factories = [];

    /**
     * @template TService of object
     *
     * @var array<non-empty-string, TService>
     */
    private array $instances = [];

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
     *
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
     * @param class-string<TService> $id
     * @param class-string<TAlias>   $alias
     *
     * @throws AliasNameAndServiceNameCannotBeTheSameException
     */
    #[Override]
    public function alias(string $id, string $alias): void
    {
        if ($alias === $id) {
            throw new AliasNameAndServiceNameCannotBeTheSameException($alias);
        }

        $this->assertValidService($id);

        $this->assertValidService($alias);

        $this->aliases[$alias] = $id;
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
     * @param class-string<TService> $id
     * @param list<TArgument>        $arguments
     *
     * @throws CircularDependencyException
     * @throws ClassNotInstantiableException
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws Throwable
     *
     * @return TService
     *
     */
    #[Override]
    public function build(string $id, array $arguments = []): object
    {
        $service = $this->resolve($id);

        if (self::class === $service) {
            return $this;
        }
        //        if (array_key_exists($service, $this->instances)) {
        //            /** @var TService */
        //            return $this->instances[$service];
        //        }

        if (array_key_exists($service, $this->dependencies)) {
            throw new CircularDependencyException(sprintf(
                'Class: %s -> %s',
                implode(' -> ', array_keys($this->dependencies)),
                $service
            ));
        }

        $this->dependencies[$service] = true;

        try {
            $instance = $this->instantiate($service, $arguments);
        } finally {
            unset($this->dependencies[$service]);
        }

        return $this->decorate($service, $instance);
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
        if (is_string($callable) && (class_exists($callable) || interface_exists($callable))) {
            $instance = $this->get($callable);

            if (! is_callable($instance)) {
                throw new InvokableClassMustBeCallableException($callable);
            }

            $callable = $instance;
        }

        $reflectionParameters = match (true) {
            $callable instanceof Closure => $this->callClosure($callable),
            default => $this->callString($callable::class),
            is_array($callable) => $this->callArray($callable),
            is_string($callable) => $this->callString($callable),
        };

        /** @var TResult */
        return $callable(...$this->buildParameter($arguments, $reflectionParameters));
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
     * @param class-string<TService>                     $id
     * @param class-string<ExtensionInterface<TService>> $extension
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     */
    #[Override]
    public function extend(string $id, string $extension): void
    {
        $this->assertValidService($id);

        if (! is_a($extension, ExtensionInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Service extension "%s" for service "%s" must implement %s.',
                    mb_trim($extension),
                    mb_trim($id),
                    ExtensionInterface::class
                )
            );
        }

        $this->extensions[$id][$extension] = true;
    }

    /**
     * Provide a FactoryInterface for a service.
     *
     * @template TService of object
     *
     * @param class-string<TService>                   $id
     * @param class-string<FactoryInterface<TService>> $factory
     *
     * @throws Throwable
     */
    #[Override]
    public function factory(string $id, string $factory): void
    {
        $this->assertValidService($id);

        if (! is_a($factory, FactoryInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Factory "%s" for service "%s" must implement %s.',
                    mb_trim($factory),
                    mb_trim($id),
                    FactoryInterface::class
                )
            );
        }

        $this->factories[$id] = $factory;
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $id
     *
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws ContainerNotFoundExceptionInterface
     *
     * @return TService
     */
    #[Override]
    public function get(string $id): object
    {
        $service = $this->resolve($id);

        if (array_key_exists($service, $this->instances)) {
            /** @var TService */
            return $this->instances[$service];
        }

        if (! array_key_exists($service, $this->factories)) {
            if (! class_exists($service)) {
                throw new ServiceNotFoundException(
                    [] !== $this->dependencies
                        ? sprintf(
                            'Service "%s" not found, required by "%s".',
                            $service,
                            array_key_last($this->dependencies)
                        ) : $service
                );
            }

            /** @var FactoryInterface<TService> $factoryInstance */
            return $this->instances[$service] = $this->build($service);
        }

        $instance = $this->call($this->factories[$service]);

        return $this->instances[$service] = $this->decorate($service, $instance);
    }

    #[Override]
    public function has(string $id): bool
    {
        $this->assertValidService($id);

        try {
            $this->get($id);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    #[Override]
    public function reset(): void
    {
        $this->aliases = [
            ContainerInterface::class => self::class,
        ];

        $this->bindings = [];
        $this->definitions = [];
        $this->dependencies = [];
        $this->extensions = [];
        $this->factories = [];
        $this->instances = [
            self::class => $this,
        ];

        $this->define(ComposerExtraDefinition::class);
    }

    #[Override]
    public function set(string $id, object $value): void
    {
        $this->assertValidService($id);

        $this->instances[$id] = $value;
    }

    #[Override]
    public function unset(string $id): void
    {
        // Remove aliases pointing to or named as $id
        foreach (array_keys($this->aliases) as $alias) {
            if ($alias === $id || $this->aliases[$alias] === $id) {
                unset($this->aliases[$alias]);
            }
        }

        unset($this->instances[$id], $this->extensions[$id], $this->factories[$id], $this->definitions[$id]);
    }

    private function assertValidService(string $class): void
    {
        if (! class_exists($class)) {
            if (! interface_exists($class)) {
                if (! enum_exists($class)) {
                    throw new ServiceNotFoundException(
                        sprintf('Service "%s" is not a valid class, interface, or enum.', $class)
                    );
                }
            }
        }
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
    private function buildParameter(
        array $arguments = [],
        array $reflectionParameters = [],
        ?ReflectionClass $reflectionClass = null
    ): array {
        $reflectionParameters = $reflectionClass instanceof ReflectionClass
            ? $this->processReflectionClass($reflectionClass)
            : $reflectionParameters;

        /** @var list<TArgument|TService> $parameters */
        $parameters = [];

        foreach ($reflectionParameters as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();

            $parameterPosition = $reflectionParameter->getPosition();

            if (array_key_exists($parameterName, $arguments)) {
                /** @var TArgument $argument */
                $argument = $arguments[$parameterName];

                unset($arguments[$parameterName]);

                $parameters[$parameterPosition] = $argument;

                continue;
            }

            if (array_key_exists($parameterPosition, $arguments)) {
                /** @var TArgument $argument */
                $argument = $arguments[$parameterPosition];

                unset($arguments[$parameterPosition]);

                $parameters[$parameterPosition] = $argument;

                continue;
            }

            if ($reflectionParameter->isOptional()) {
                continue;
            }

            $reflectionType = $reflectionParameter->getType();
            $isVendorClass = $reflectionType instanceof ReflectionNamedType && ! $reflectionType->isBuiltin();
            $isDefaultValueAvailable = $reflectionParameter->isDefaultValueAvailable();

            if (! $isVendorClass && ! $isDefaultValueAvailable) {
                $name = $reflectionParameter->getDeclaringFunction()->getName();

                $isFunction = function_exists($name);

                throw new UnresolvableParameterException(sprintf(
                    'Unresolvable %s parameter "$%s" in "%s%s()"; does not have a default value.',
                    $isFunction ? 'function' : 'class',
                    $parameterName,
                    $isFunction ? $name : $reflectionParameter->getDeclaringClass()?->getName() ?? '',
                    $isFunction ? '' : '::' . $name
                ));
            }

            if (! $isVendorClass) {
                /** @var TArgument $argument */
                $argument = $reflectionParameter->getDefaultValue();
                $parameters[$parameterPosition] = $argument;

                continue;
            }

            /** @var class-string<TService> $reflectionTypeName */
            $reflectionTypeName = $reflectionType->getName();

            $parameters[$parameterPosition] = match (true) {
                default => match (true) {
                    default =>  $this->get($reflectionTypeName),
                    $isDefaultValueAvailable => $reflectionParameter->getDefaultValue(),
                },
                array_key_exists($reflectionTypeName, $this->instances) => $this->instances[$reflectionTypeName],
            };
        }

        return $parameters;
    }

    /**
     * @template TObject of object
     *
     * @param array{0:class-string<TObject>|TObject, 1:string} $callback
     *
     * @throws Throwable
     *
     * @return list<ReflectionParameter>
     */
    private function callArray(array $callback): array
    {
        $service = $callback[0];

        if (is_object($service)) {
            $service = $service::class;
        }

        $method = $callback[1] ?? '__invoke';

        return $this->reflectClass($service)
            ->getMethod($method)
            ->getParameters();
    }

    /**
     * @throws Throwable
     *
     * @return list<ReflectionParameter>
     */
    private function callClosure(Closure $callback): array
    {
        return $this->reflectFunction($callback)->getParameters();
    }

    /**
     * @param class-string|string $callback
     *
     * @throws Throwable
     *
     * @return list<ReflectionParameter>
     */
    private function callString(string $callback): array
    {
        return match (true) {
            function_exists($callback) => $this->reflectFunction($callback)->getParameters(),
            class_exists($callback) => $this->callArray([$callback, '__invoke']),
            str_contains($callback, '::') => (function (string $callback): array {
                /** @var array{0:class-string,1:non-empty-string} $metadata */
                $metadata = explode('::', $callback, 2);

                return $this->callArray([$metadata[0], $metadata[1]]);
            })($callback),

            default => throw new InvokableClassMustBeCallableException($callback),
        };
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

        foreach (array_keys($this->extensions) as $serviceClass) {
            if (! is_a($instance, $serviceClass, true)) {
                continue;
            }

            foreach (array_keys($this->extensions[$serviceClass] ?? []) as $extension) {
                /** @var TService $instance */
                $this->call($extension, [
                    'service' => $instance,
                ]);
            }
        }

        unset($this->instances[$service]);

        return $instance;
    }

    /**
     * @template TInstantiate of object
     * @template TArgument
     *
     * @param class-string<TInstantiate> $service
     * @param list<TArgument>            $arguments
     *
     * @throws Throwable
     *
     * @return TInstantiate
     */
    private function instantiate(string $service, array $arguments = []): object
    {
        $reflectionClass = $this->reflectClass($service);
        if (! $reflectionClass->isInstantiable()) {
            throw new ClassNotInstantiableException($service);
        }

        $parameters = $this->buildParameter($arguments, [], $reflectionClass);

        try {
            $instance = $reflectionClass->newInstance(...$parameters);
        } catch (Throwable $throwable) {
            throw new InstantiatorException($throwable->getMessage(), 127, $throwable);
        }

        return $instance;
    }

    /**
     * @template TService of object
     *
     * @param ReflectionClass<TService> $reflectionClass
     *
     * @throws Throwable
     *
     * @return list<ReflectionParameter>
     */
    private function processReflectionClass(ReflectionClass $reflectionClass): array
    {
        $constructor = $reflectionClass->getConstructor();
        if (! $constructor instanceof ReflectionMethod) {
            return [];
        }

        return $constructor->getParameters();
    }

    /**
     * @template TClass of object
     *
     * @param class-string<TClass>|TClass $objectOrClass
     *
     * @throws ReflectionException
     *
     * @return ReflectionClass<TClass>
     *
     */
    private function reflectClass(object|string $objectOrClass): ReflectionClass
    {
        return new ReflectionClass($objectOrClass);
    }

    /**
     * @param callable-string|Closure $function
     *
     * @throws ReflectionException
     */
    private function reflectFunction(Closure|string $function): ReflectionFunction
    {
        return new ReflectionFunction($function);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @return class-string<TService>
     */
    private function resolve(string $service): string
    {
        $this->assertValidService($service);

        while (array_key_exists($service, $this->aliases)) {
            $service = $this->aliases[$service];
        }

        if ([] === $this->dependencies) {
            return $service;
        }

        $concrete = array_key_last($this->dependencies);

        if (! isset($this->bindings[$concrete][$service])) {
            return $service;
        }

        return $this->bindings[$concrete][$service];
    }
}
