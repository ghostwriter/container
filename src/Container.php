<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Generator;
use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\BindingNotFoundException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\DependencyNotFoundException;
use Ghostwriter\Container\Exception\DontCloneContainerException;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Exception\InvokableClassMustBeCallableException;
use Ghostwriter\Container\Exception\ReflectionException;
use Ghostwriter\Container\Exception\ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException;
use Ghostwriter\Container\Exception\ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException;
use Ghostwriter\Container\Exception\ServiceMustBeAnObjectException;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ShouldNotHappenException;
use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Interface\AttributeInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use InvalidArgumentException;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

use function array_key_exists;
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
use function sprintf;
use function trim;

/**
 * @see \Tests\Unit\ContainerTest
 */
final class Container implements ContainerInterface
{
    private Aliases $aliases;

    private Bindings $bindings;

    private Builders $builders;

    private Dependencies $dependencies;

    private Extensions $extensions;

    private Factories $factories;

    private Instances $instances;

    private Providers $providers;

    private Tags $tags;

    private function __construct()
    {
        // Singleton
        $this->purge();
    }

    public function __destruct()
    {
        $this->purge();
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

    /**
     * @throws DontSerializeContainerException
     */
    public function __serialize(): array
    {
        throw new DontSerializeContainerException();
    }

    /**
     * @template TMixed
     *
     * @param array<TMixed> $_
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
     * @param class-string<TService> $service
     * @param class-string<TAlias>   $alias
     *
     * @throws AliasNameMustBeNonEmptyStringException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws AliasNameAndServiceNameCannotBeTheSameException
     */
    #[Override]
    public function alias(string $service, string $alias): void
    {
        if (trim($alias) === '') {
            throw new AliasNameMustBeNonEmptyStringException();
        }

        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($alias === $service) {
            throw new AliasNameAndServiceNameCannotBeTheSameException($alias);
        }

        $this->aliases->set($alias, $service);
    }

    /**
     * @template TConcrete of object
     * @template TAbstract of object
     * @template TImplementation of object
     *
     * @param class-string<TConcrete>       $concrete
     * @param class-string<TAbstract>       $service
     * @param class-string<TImplementation> $implementation
     */
    #[Override]
    public function bind(string $concrete, string $service, string $implementation): void
    {
        if (trim($concrete) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($concrete)) {
            throw new ServiceNotFoundException($concrete);
        }

        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($service) && ! interface_exists($service)) {
            throw new ServiceNotFoundException($service);
        }

        if (trim($implementation) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($implementation) && ! interface_exists($implementation)) {
            throw new ServiceNotFoundException($implementation);
        }

        $this->bindings->set($concrete, $service, $implementation);
    }

    /**
     * @template TService of object
     * @template TArgument
     *
     * @param class-string<TService> $service
     * @param array<TArgument>       $arguments
     *
     * @throws CircularDependencyException
     * @throws ClassNotInstantiableException
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws Throwable
     *
     * @return TService
     *
     */
    #[Override]
    public function build(string $service, array $arguments = []): object
    {
        $class = $this->resolve($service);

        if ($this->dependencies->has($class)) {
            throw new CircularDependencyException(sprintf(
                'Class: %s -> %s',
                implode(' -> ', $this->dependencies->toArray()),
                $class
            ));
        }

        $this->dependencies->set($class);

        $instance = $this->instantiate($class, $arguments);

        $this->dependencies->unset($class);

        /** @var TService */
        return $this->applyExtensions($class, $instance);
    }

    /**
     * @template TService of object
     * @template TArgument
     * @template TResult
     *
     * @param array{0:(class-string<TService>|TService),1:'__invoke'|string}|callable|callable-string|Closure(TArgument...):TResult|TService $callback
     * @param array<TArgument>                                                                                                               $arguments
     *
     * @throws Throwable
     *
     * @return TResult
     *
     */
    #[Override]
    public function call(callable $callback, array $arguments = []): mixed
    {
        $reflectionParameters = match (true) {
            $callback instanceof Closure => $this->callClosure($callback),
            default => $this->callString($callback::class),
            is_array($callback) => $this->callArray($callback),
            is_string($callback) => $this->callString($callback),
        };

        $parameters = $this->buildParameter($reflectionParameters, $arguments);

        /** @var TResult */
        return $callback(...$parameters);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>                     $service
     * @param class-string<ExtensionInterface<TService>> $extension
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException
     */
    #[Override]
    public function extend(string $service, string $extension): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! is_a($extension, ExtensionInterface::class, true)) {
            throw new ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException($extension);
        }

        $this->extensions->set($service, $extension);
    }

    /**
     * Provide a FactoryInterface for a service.
     *
     * @template TService of object
     *
     * @param class-string<TService>                   $service
     * @param class-string<FactoryInterface<TService>> $factory
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException
     * @throws Throwable
     */
    #[Override]
    public function factory(string $service, string $factory): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! is_a($factory, FactoryInterface::class, true)) {
            throw new ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException($factory);
        }

        $this->factories->set($service, $factory);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     *
     * @return TService
     */
    #[Override]
    public function get(string $service): object
    {
        $class = $this->resolve($service);

        /** @var TService */
        return match (true) {
            default => $this->applyExtensions($class, match (true) {
                default => match (true) {
                    class_exists($class) => $this->build($class),
                    default => throw new ServiceNotFoundException(
                        $this->dependencies->found() ?
                        sprintf(
                            'Service "%s" not found, required by "%s".',
                            $class,
                            $this->dependencies->last()
                        ) : $class
                    ),
                },

                $this->factories->has($class) => $this->invoke($this->factories->get($class)),

                $this->builders->has($class) => (
                    /**
                     * @throws ServiceMustBeAnObjectException
                     * @throws Throwable
                     *
                     * @return TService
                     *
                     */
                    function (string $class): object {
                        /** @var class-string<TService> $class */
                        $builder = $this->builders->get($class);

                        /** @var null|TService $instance */
                        $instance = $this->call($builder);

                        if (! is_object($instance)) {
                            throw new ServiceMustBeAnObjectException($class);
                        }

                        return $instance;
                    }
                )($class),
            }),

            $this->instances->has($class) => $this->instances->get($class),
        };
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     */
    #[Override]
    public function has(string $service): bool
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        return match (true) {
            $this->aliases->has($service),
            $this->builders->has($service),
            $this->factories->has($service),
            $this->instances->has($service) => true,
            default => $this->bindings->contains($service),
        };
    }

    /**
     * @template TService of object
     * @template TArgument
     * @template TResult
     *
     * @param class-string<TService> $invokable
     * @param array<TArgument>       $arguments
     *
     * @throws InvokableClassMustBeCallableException
     * @throws Throwable
     *
     * @return TResult
     *
     */
    #[Override]
    public function invoke(string $invokable, array $arguments = []): mixed
    {
        /** @var Closure(TArgument...):TResult&TService $callable */
        $callable = $this->get($invokable);

        if (! is_callable($callable)) {
            throw new InvokableClassMustBeCallableException($invokable);
        }

        /** @var TResult */
        return $this->call($callable, $arguments);
    }

    /**
     * @param class-string<ServiceProviderInterface> $serviceProvider
     *
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException
     * @throws Throwable
     */
    #[Override]
    public function provide(string $serviceProvider): void
    {
        if (! is_a($serviceProvider, ServiceProviderInterface::class, true)) {
            throw new ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException($serviceProvider);
        }

        if ($this->providers->has($serviceProvider)) {
            throw new ServiceProviderAlreadyRegisteredException($serviceProvider);
        }

        $this->providers->add($serviceProvider);

        $this->invoke($serviceProvider);
    }

    #[Override]
    public function purge(): void
    {
        $this->aliases = Aliases::new();
        $this->bindings = Bindings::new();
        $this->builders = Builders::new();
        $this->dependencies = Dependencies::new();
        $this->extensions = Extensions::new();
        $this->factories = Factories::new();
        $this->instances = Instances::new([
            ContainerInterface::class => $this,
        ]);
        $this->providers = Providers::new();
        $this->tags = Tags::new();
    }

    /**
     * @template TAbstract of object
     * @template TConcrete of object
     * @template TTag of object
     *
     * @param class-string<TAbstract>   $abstract
     * @param class-string<TConcrete>   $concrete
     * @param array<class-string<TTag>> $tags
     *
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     */
    #[Override]
    public function register(string $abstract, ?string $concrete = null, array $tags = []): void
    {
        $concrete ??= $abstract;

        if (trim($abstract) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (trim($concrete) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($tags !== []) {
            $this->tags->set($abstract, $tags);
        }

        $this->builders->set(
            $concrete,
            /**
             * @throws Throwable
             *
             * @return TAbstract|TConcrete
             *
             */
            static fn (ContainerInterface $container): object => $container->build($concrete)
        );

        if ($abstract === $concrete) {
            return;
        }

        $this->aliases->set($abstract, $concrete);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     */
    #[Override]
    public function remove(string $service): void
    {
        $this->aliases->unset($service);
        $this->builders->unset($service);
        $this->extensions->unset($service);
        $this->factories->unset($service);
        $this->instances->unset($service);
        $this->providers->unset($service);
        $this->tags->unset($service);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>                          $service
     * @param (Closure(ContainerInterface):TService)|TService $value
     * @param list<non-empty-string>                          $tags
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceTagMustBeNonEmptyStringException
     * @throws ExceptionInterface
     */
    #[Override]
    public function set(string $service, callable|object $value, array $tags = []): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($tags !== []) {
            $this->tags->set($service, $tags);
        }

        if (! $value instanceof Closure) {
            $this->instances->set($service, $value);

            return;
        }

        /** @var Closure(ContainerInterface):TService $value */
        $this->builders->set($service, $value);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>            $service
     * @param non-empty-array<non-empty-string> $tags
     *
     * @throws ExceptionInterface
     */
    #[Override]
    public function tag(string $service, array $tags): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        $this->tags->set($service, $tags);
    }

    /**
     * @template TService of object
     * @template TTag of object
     *
     * @param class-string<TTag> $tag
     *
     * @throws Throwable
     *
     * @return Generator<class-string<TService>,TService>
     *
     */
    #[Override]
    public function tagged(string $tag): Generator
    {
        if (trim($tag) === '') {
            throw new ServiceTagMustBeNonEmptyStringException();
        }

        /** @var class-string<TService> $service */
        foreach ($this->tags->get($tag) as $service) {
            yield $service => $this->get($service);
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>            $service
     * @param non-empty-array<non-empty-string> $tags
     */
    #[Override]
    public function untag(string $service, array $tags): void
    {
        $this->tags->remove($service, $tags);
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
     *
     */
    private function applyExtensions(string $service, object $instance): object
    {
        $this->instances->set($service, $instance);

        $extensions = $this->extensions->all();

        if ($extensions === []) {
            return $instance;
        }

        foreach (array_keys($extensions) as $serviceName) {
            if (! $instance instanceof $serviceName) {
                continue;
            }

            foreach ($extensions[$serviceName] ?? [] as $extension => $_) {
                /** @var TService $instance */
                $instance = $this->invoke($extension, [$this, $instance]);
            }
        }

        $this->instances->set($service, $instance);

        return $instance;
    }

    /**
     * @template TArgument
     * @template TService of object
     *
     * @param array<ReflectionParameter> $reflectionParameters
     * @param array<TArgument>           $arguments
     *
     * @throws Throwable
     *
     * @return array<TArgument|TService>
     *
     */
    private function buildParameter(array $reflectionParameters = [], array $arguments = []): array
    {
        /** @var array<TArgument|TService> $parameters */
        $parameters = [];

        foreach ($reflectionParameters as $reflectionParameter) {
            $reflectionType = $reflectionParameter->getType();

            $isVendorClass = $reflectionType instanceof ReflectionNamedType && ! $reflectionType->isBuiltin();

            $reflectionTypeName = '';

            if ($isVendorClass) {
                /** @var class-string<TService> $reflectionTypeName */
                $reflectionTypeName = $reflectionType->getName();

                $this->processReflectionParameter($reflectionTypeName, $reflectionParameter);
            }

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

            $isDefaultValueAvailable = $reflectionParameter->isDefaultValueAvailable();

            if (! $isVendorClass && ! $isDefaultValueAvailable) {
                $name = $reflectionParameter->getDeclaringFunction()
                    ->getName();

                $isFunction = function_exists($name);

                throw new UnresolvableParameterException(sprintf(
                    'Unresolvable %s parameter "$%s" in "%s%s()"; does not have a default value.',
                    $isFunction ? 'function' : 'class',
                    $parameterName,
                    $isFunction ? $name : $reflectionParameter->getDeclaringClass()?->getName() ?? '',
                    $isFunction ? '' : '::' . $name
                ));
            }

            /**
             * @var class-string<TService> $reflectionTypeName
             * @var TService               $service
             */
            $service = match (true) {
                default => match (true) {
                    default =>  $this->get($reflectionTypeName),
                    $isDefaultValueAvailable => $reflectionParameter->getDefaultValue(),
                },
                $this->instances->has($reflectionTypeName) => $this->instances->get($reflectionTypeName),
            };

            $parameters[$parameterPosition] = $service;
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
     * @return ReflectionParameter[]
     *
     * @psalm-return list<ReflectionParameter>
     */
    private function callArray(array $callback): array
    {
        $class = $callback[0];

        if (is_object($class)) {
            $class = $class::class;
        }

        $method = $callback[1] ?? '__invoke';

        return $this->reflectClass($class)
            ->getMethod($method)
            ->getParameters();
    }

    /**
     * @throws Throwable
     *
     * @return ReflectionParameter[]
     *
     * @psalm-return list<ReflectionParameter>
     */
    private function callClosure(Closure $callback): array
    {
        return $this->reflectFunction($callback)
            ->getParameters();
    }

    /**
     * @param class-string|string $callback
     *
     * @throws Throwable
     *
     * @return ReflectionParameter[]
     *
     * @psalm-return list<ReflectionParameter>
     */
    private function callString(string $callback): array
    {
        return match (true) {
            function_exists($callback) => $this->reflectFunction($callback)
                ->getParameters(),

            default => (function (string $callback) {
                /**
                 * @var class-string                             $class
                 * @var '__invoke'|non-empty-string              $method
                 * @var array{0:class-string,1:non-empty-string} $metadata
                 */
                $metadata = explode('::', $callback, 2);

                $class = $metadata[0];

                $method = $metadata[1] ?? '__invoke';

                return $this->callArray([$class, $method]);
            })($callback),
        };
    }

    /**
     * @template TInstantiate of object
     * @template TArgument
     *
     * @param class-string<TInstantiate> $service
     * @param array<TArgument>           $arguments
     *
     * @throws Throwable
     *
     * @return TInstantiate
     *
     */
    private function instantiate(string $service, array $arguments = []): object
    {
        $reflectionClass = $this->reflectClass($service);

        if (! $reflectionClass->isInstantiable()) {
            throw new ClassNotInstantiableException($service);
        }

        $has = $this->has($service);

        $this->processReflectionClass($reflectionClass);

        if (! $has && $this->has($service)) {
            return $this->get($service);
        }

        $constructor = $reflectionClass->getConstructor();

        $parameters = $constructor === null ? [] : $this->buildParameter(
            $constructor->getParameters(),
            $arguments
        );

        if (! $has && $this->has($service)) {
            return $this->get($service);
        }

        try {
            return $reflectionClass->newInstance(...$parameters);
        } catch (Throwable $throwable) {
            throw new InstantiatorException($throwable->getMessage(), 127, $throwable);
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>                  $class
     * @param ReflectionAttribute<AttributeInterface> $reflectionAttribute
     *
     * @throws Throwable
     */
    private function processReflectionAttribute(string $class, ReflectionAttribute $reflectionAttribute): void
    {
        $instance = $reflectionAttribute->newInstance();

        match (true) {
            default => throw new ShouldNotHappenException(),
            $instance instanceof Extension => $this->extend($class, $instance->service()),

            $instance instanceof Factory => $this->factory($class, $instance->service()),

            $instance instanceof Inject => match (true) {
                default => $this->register($class, $instance->service()),

                $instance->concrete !== null => $this->bind($instance->concrete(), $class, $instance->service()),
            },
        };
    }

    /**
     * @template TService of object
     *
     * @param ReflectionClass<TService> $reflectionClass
     *
     * @throws Throwable
     */
    private function processReflectionClass(ReflectionClass $reflectionClass): void
    {
        $class = $reflectionClass->getName();

        foreach (
            $reflectionClass->getAttributes(
                AttributeInterface::class,
                ReflectionAttribute::IS_INSTANCEOF
            ) as $reflectionAttribute
        ) {
            $this->processReflectionAttribute($class, $reflectionAttribute);
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $class
     *
     * @throws Throwable
     */
    private function processReflectionParameter(string $class, ReflectionParameter $reflectionParameter): void
    {
        foreach (
            $reflectionParameter->getAttributes(
                AttributeInterface::class,
                ReflectionAttribute::IS_INSTANCEOF
            ) as $reflectionAttribute
        ) {
            $this->processReflectionAttribute($class, $reflectionAttribute);
        }
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
        try {
            return new ReflectionClass($objectOrClass);
        } catch (Throwable $throwable) {
            throw new ReflectionException($throwable->getMessage(), 127, $throwable);
        }
    }

    /**
     * @param callable-string|Closure $function
     *
     * @throws ReflectionException
     */
    private function reflectFunction(Closure|string $function): ReflectionFunction
    {
        try {
            return new ReflectionFunction($function);
        } catch (Throwable $throwable) {
            throw new ReflectionException($throwable->getMessage(), 127, $throwable);
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws DependencyNotFoundException
     * @throws BindingNotFoundException
     *
     * @return class-string<TService>
     *
     */
    private function resolve(string $service): string
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        while ($this->aliases->has($service)) {
            /** @var class-string<TService> $service */
            $service = $this->aliases->get($service);
        }

        if ($this->dependencies->found()) {
            $concrete = $this->dependencies->last();

            if ($this->bindings->has($concrete, $service)) {
                /** @var class-string<TService> */
                return $this->bindings->get($concrete, $service);
            }
        }

        /** @var class-string<TService> $service */
        return $service;
    }

    public static function getInstance(): self
    {
        static $instance;

        if ($instance instanceof self) {
            return $instance;
        }

        return $instance = new self();
    }
}
