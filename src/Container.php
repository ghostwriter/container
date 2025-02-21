<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Generator;
use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Attribute\Provider;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\AliasNotFoundException;
use Ghostwriter\Container\Exception\BindingNotFoundException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\DependencyNotFoundException;
use Ghostwriter\Container\Exception\DontCloneContainerException;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Exception\InvokableClassMustBeCallableException;
use Ghostwriter\Container\Exception\ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException;
use Ghostwriter\Container\Exception\ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException;
use Ghostwriter\Container\Exception\ServiceMustBeAnObjectException;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
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
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Tests\Unit\ContainerTest;
use Throwable;

use function array_key_exists;
use function array_keys;
use function class_exists;
use function explode;
use function function_exists;
use function implode;
use function is_a;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function sprintf;
use function str_contains;

/**
 * @see ContainerTest
 */
final readonly class Container implements ContainerInterface
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
        $this->aliases = Aliases::new();
        $this->bindings = Bindings::new();
        $this->builders = Builders::new();
        $this->dependencies = Dependencies::new();
        $this->extensions = Extensions::new();
        $this->factories = Factories::new();
        $this->instances = Instances::new([
            self::class => $this,
            ContainerInterface::class => $this,
        ]);
        $this->providers = Providers::new();
        $this->tags = Tags::new();
    }

    public static function getInstance(): self
    {
        static $instance;

        if ($instance instanceof self) {
            return $instance;
        }

        return $instance = new self();
    }

    public function __destruct()
    {
        $this->clear();
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
        $this->aliases->set($service, $alias);
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
        $this->bindings->set($concrete, $service, $implementation);
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
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws Throwable
     *
     * @return TService
     */
    #[Override]
    public function build(string $service, array $arguments = []): object
    {
        $serviceName = $this->resolve($service);
        if (is_a($service, ContainerInterface::class, true)) {
            return $this;
        }

        if ($this->dependencies->has($serviceName)) {
            throw new CircularDependencyException(sprintf(
                'Class: %s -> %s',
                implode(' -> ', $this->dependencies->toArray()),
                $serviceName
            ));
        }

        $this->dependencies->set($serviceName);

        $instance = $this->instantiate($serviceName, $arguments);

        $this->dependencies->unset($serviceName);

        /** @var TService */
        return $this->applyExtensions($serviceName, $instance);
    }

    /**
     * @template TService of object
     * @template TArgument
     * @template TResult
     *
     * @param array{0:(class-string<TService>|TService),1:'__invoke'|string}|callable|callable-string|Closure(TArgument...):TResult|TService $callback
     * @param list<TArgument>                                                                                                                $arguments
     *
     * @throws Throwable
     *
     * @return TResult
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

        $this->processReflectionParameters(...$reflectionParameters);

        $parameters = $this->buildParameter($reflectionParameters, $arguments);

        /** @var TResult */
        return $callback(...$parameters);
    }

    #[Override]
    public function clear(): void
    {
        $this->aliases->clear();
        $this->bindings->clear();
        $this->builders->clear();
        $this->dependencies->clear();
        $this->extensions->clear();
        $this->factories->clear();
        $this->instances->clear();
        $this->providers->clear();
        $this->tags->clear();
    }

    #[Override]
    public function define(string $service, callable $value, array $tags = []): void
    {
        if ([] !== $tags) {
            $this->tags->set($service, $tags);
        }

        $this->builders->set($service, $value);
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
        /** @var class-string<TService> $class */
        $class = $this->resolve($service);

        /** @var TService */
        return match (true) {
            default => $this->applyExtensions($class, match (true) {
                default => match (true) {
                    default => match (true) {
                        class_exists($class) => $this->build($class),
                        default => throw new ServiceNotFoundException(
                            $this->dependencies->missing()
                            ? sprintf(
                                'Service "%s" not found, required by "%s".',
                                $class,
                                $this->dependencies->last()
                            ) : $class
                        ),
                    },
                    $this->builders->has($class) => $this->buildObject($class),
                },
                $this->factories->has($class) => $this->invoke($this->factories->get($class)),
            }),
            $this->instances->has($class) => $this->instances->get($class),
            is_a($class, ContainerInterface::class, true) => $this,
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
        return match (true) {
            is_a($service, ContainerInterface::class, true),
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
     * @param list<TArgument>        $arguments
     *
     * @throws InvokableClassMustBeCallableException
     * @throws Throwable
     *
     * @return TResult
     */
    #[Override]
    public function invoke(string $invokable, array $arguments = []): mixed
    {
        /** @var callable(TArgument...):TResult&TService $callable */
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
     */
    #[Override]
    public function provide(string $serviceProvider): void
    {
        if ($this->providers->has($serviceProvider)) {
            throw new ServiceProviderAlreadyRegisteredException($serviceProvider);
        }

        $this->providers->add($serviceProvider, $this);
    }

    #[Override]
    public function remove(string $service): void
    {
        $this->aliases->unset($service);
        $this->builders->unset($service);
        $this->instances->unset($service);
        $this->extensions->unset($service);
        $this->tags->unset($service);
        $this->factories->unset($service);
    }

    #[Override]
    public function set(string $service, object $value, array $tags = []): void
    {
        if ([] !== $tags) {
            $this->tags->set($service, $tags);
        }

        $this->instances->set($service, $value);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>           $service
     * @param non-empty-list<non-empty-string> $tags
     *
     * @throws ExceptionInterface
     */
    #[Override]
    public function tag(string $service, array $tags): void
    {
        $this->tags->set($service, $tags);
    }

    /**
     * @template TService of object
     *
     * @param non-empty-string $tag
     *
     * @throws ServiceTagNotFoundException
     * @throws Throwable
     *
     * @return Generator<class-string<TService>,TService>
     */
    #[Override]
    public function tagged(string $tag): Generator
    {
        /** @var class-string<TService> $service */
        foreach ($this->tags->get($tag) as $service) {
            yield $service => $this->get($service);
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>           $service
     * @param non-empty-list<non-empty-string> $tags
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
     */
    private function applyExtensions(string $service, object $instance): object
    {
        $this->instances->set($service, $instance);

        $extensions = $this->extensions->all();

        if ([] === $extensions) {
            return $instance;
        }

        foreach (array_keys($extensions) as $serviceClass) {
            if (! $instance instanceof $serviceClass) {
                continue;
            }

            foreach ($extensions[$serviceClass] ?? [] as $extension => $_) {
                /** @var TService $instance */
                $instance = $this->invoke($extension, [
                    'service' => $instance,
                ]);
            }
        }

        $this->instances->set($service, $instance);

        return $instance;
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
    private function buildObject(string $service): object
    {
        $builder = $this->builders->get($service);

        $instance = $this->call($builder);

        if (is_object($instance)) {
            return $instance;
        }

        throw new ServiceMustBeAnObjectException($service);
    }

    /**
     * @template TArgument
     * @template TService of object
     *
     * @param list<ReflectionParameter> $reflectionParameters
     * @param list<TArgument>           $arguments
     *
     * @throws Throwable
     *
     * @return list<TArgument|TService>
     */
    private function buildParameter(array $reflectionParameters = [], array $arguments = []): array
    {
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
                $this->instances->has($reflectionTypeName) => $this->instances->get($reflectionTypeName),
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

        $reflectionParameters = $this->processReflectionClass($reflectionClass);

        if ($this->factories->has($service)) {
            return $this->get($service);
        }

        if (! $reflectionClass->isInstantiable()) {
            throw new ClassNotInstantiableException($service);
        }

        $parameters = $this->buildParameter($reflectionParameters, $arguments);

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
        $class = $reflectionClass->getName();

        foreach (
            $reflectionClass->getAttributes(
                AttributeInterface::class,
                ReflectionAttribute::IS_INSTANCEOF
            ) as $reflectionAttribute
        ) {
            $this->processReflectionClassAttribute($class, $reflectionAttribute);
        }

        $constructor = $reflectionClass->getConstructor();
        if (! $constructor instanceof ReflectionMethod) {
            return [];
        }

        $reflectionParameters = $constructor->getParameters();

        $this->processReflectionClassParameters($class, ...$reflectionParameters);

        return $reflectionParameters;
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>                  $class
     * @param ReflectionAttribute<AttributeInterface> $reflectionAttribute
     *
     * @throws Throwable
     */
    private function processReflectionClassAttribute(string $class, ReflectionAttribute $reflectionAttribute): void
    {
        $attribute = $reflectionAttribute->newInstance();

        if (! $attribute instanceof AttributeInterface) {
            return;
        }

        $name = $attribute->name();

        match (true) {
            $attribute instanceof Provider => match (true) {
                $this->providers->has($name) => null,
                default => $this->providers->add($name, $this),
            },
            $attribute instanceof Extension => $this->extend($class, $name),
            $attribute instanceof Factory => $this->factory($class, $name),
            default => throw new ShouldNotHappenException($attribute::class),
        };
    }

    /**
     * @template TService of object
     *
     * @throws Throwable
     */
    private function processReflectionClassParameters(
        string $concrete,
        ReflectionParameter ...$reflectionParameters
    ): void {
        foreach ($reflectionParameters as $reflectionParameter) {
            $reflectionType = $reflectionParameter->getType();

            if (! $reflectionType instanceof ReflectionNamedType) {
                continue;
            }

            if ($reflectionType->isBuiltin()) {
                continue;
            }

            /** @var class-string<TService> $service */
            $service = $reflectionType->getName();

            foreach ($reflectionParameter->getAttributes(
                AttributeInterface::class,
                ReflectionAttribute::IS_INSTANCEOF
            ) as $reflectionAttribute) {
                $attribute = $reflectionAttribute->newInstance();

                if (! $attribute instanceof Inject) {
                    continue;
                }

                if ($this->bindings->has($concrete, $service)) {
                    continue;
                }

                $this->bindings->set($concrete, $service, $attribute->name());
            }
        }

    }

    /**
     * @template TService of object
     *
     * @throws Throwable
     */
    private function processReflectionParameters(ReflectionParameter ...$reflectionParameters): void
    {
        foreach ($reflectionParameters as $reflectionParameter) {
            $reflectionType = $reflectionParameter->getType();

            if (! $reflectionType instanceof ReflectionNamedType) {
                continue;
            }

            if ($reflectionType->isBuiltin()) {
                continue;
            }

            /** @var class-string<TService> $class */
            $class = $reflectionType->getName();

            foreach ($reflectionParameter->getAttributes(
                AttributeInterface::class,
                ReflectionAttribute::IS_INSTANCEOF
            ) as $reflectionAttribute) {
                $attribute = $reflectionAttribute->newInstance();

                if ($attribute instanceof Inject) {
                    match (true) {
                        $this->aliases->has($class) => null,
                        default => $this->aliases->set($attribute->name(), $class),
                    };

                    continue;
                }

                throw new ShouldNotHappenException($attribute::class);
            }
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
     * @throws DependencyNotFoundException
     * @throws BindingNotFoundException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws AliasNotFoundException
     *
     * @return class-string<TService>
     */
    private function resolve(string $service): string
    {
        while ($this->aliases->has($service)) {
            $service = $this->aliases->get($service);
        }

        if ($this->dependencies->isEmpty()) {
            return $service;
        }

        $concrete = $this->dependencies->last();
        if ($this->bindings->has($concrete, $service)) {
            return $this->bindings->get($concrete, $service);
        }

        return $service;
    }
}
