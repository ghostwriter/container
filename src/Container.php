<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Generator;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\DontCloneContainerException;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Exception\ServiceExtensionAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException;
use Ghostwriter\Container\Exception\ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException;
use Ghostwriter\Container\Exception\ServiceMustBeAnObjectException;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use InvalidArgumentException;
use Throwable;

use function array_key_exists;
use function array_key_last;
use function array_keys;
use function array_reduce;
use function class_exists;
use function implode;
use function in_array;
use function interface_exists;
use function is_a;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function mb_strpos;
use function mb_substr;
use function sprintf;
use function str_contains;
use function trim;

/**
 * @see \Ghostwriter\ContainerTests\Unit\ContainerTest
 */
final class Container implements ContainerInterface
{
    /**
     * @var array<string,string>
     */
    private array $aliases = [
        ContainerInterface::class => self::class,
    ];

    /**
     * @var array<class-string,array<class-string,class-string>>
     */
    private array $bindings = [];

    /**
     * @var array<class-string,bool>
     */
    private array $dependencies = [];

    /**
     * @var array<class-string,list<ExtensionInterface>>
     */
    private array $extensions = [];

    /**
     * @var array<class-string,Closure(ContainerInterface):object>
     */
    private array $factories = [];

    /**
     * @var array<class-string,object>
     */
    private array $instances = [];

    /**
     * @var array<class-string<ServiceProviderInterface>,bool>
     */
    private array $providers = [];

    /**
     * @var array<class-string,class-string>
     */
    private array $services = [];

    /**
     * @var array<string,array<string,string>>
     */
    private array $tags = [];

    private readonly Instantiator $instantiator;

    private readonly ParameterBuilder $parameterBuilder;

    private readonly Reflector $reflector;

    private static self $instance;

    private function __construct()
    {
        $this->reflector = Reflector::new();

        $this->parameterBuilder = ParameterBuilder::new($this);

        $this->instantiator = Instantiator::new($this->reflector, $this->parameterBuilder);
    }

    /**
     * Remove all registered services from this container and reset the default services.
     */
    public function __destruct()
    {
        $this->aliases = [
            ContainerInterface::class => self::class,
        ];
        $this->bindings = [];
        $this->dependencies = [];
        $this->extensions = [];
        $this->factories = [];
        $this->instances = [];
        $this->providers = [];
        $this->services = [];
        $this->tags = [];
    }

    /**
     * @throws ExceptionInterface if "__clone()" method is called
     */
    public function __clone(): void
    {
        throw new DontCloneContainerException();
    }

    /**
     * @throws ExceptionInterface if "__serialize()" method is called
     */
    public function __serialize(): array
    {
        throw new DontSerializeContainerException();
    }

    /**
     * @template TMixed
     *
     * @param array<TMixed> $data
     *
     * @throws ExceptionInterface if "__unserialize()" method is called
     */
    public function __unserialize(array $data): void
    {
        throw new DontUnserializeContainerException();
    }

    public function alias(string $name, string $service): void
    {
        if (trim($name) === '') {
            throw new AliasNameMustBeNonEmptyStringException();
        }

        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($name === $service) {
            throw new AliasNameAndServiceNameCannotBeTheSameException($name);
        }

        $this->aliases[$name] = $service;
    }

    /**
     * @template TConcreteClass of object
     * @template TAbstractClass of object
     * @template TImplementationClass of object
     *
     * @param class-string<TConcreteClass>       $concrete
     * @param class-string<TAbstractClass>       $abstract
     * @param class-string<TImplementationClass> $implementation
     */
    public function bind(string $concrete, string $abstract, string $implementation): void
    {
        if (trim($concrete) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($concrete)) {
            throw new ServiceNotFoundException($concrete);
        }

        if (trim($abstract) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($abstract) &&
            ! interface_exists($abstract)
        ) {
            throw new ServiceNotFoundException($abstract);
        }

        if (trim($implementation) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($implementation) &&
            ! interface_exists($implementation)
        ) {
            throw new ServiceNotFoundException($implementation);
        }

        $this->bindings[$concrete][$abstract] = $implementation;
    }

    /**
     * @template TArgument
     * @template TService of object
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
     */
    public function build(string $service, array $arguments = []): object
    {
        $class = $this->resolve($service);

        if (is_a($class, ContainerInterface::class, true)) {
            return $this;
        }

        if (array_key_exists($class, $this->dependencies)) {
            throw new CircularDependencyException(sprintf(
                'Class: %s -> %s',
                implode(' -> ', array_keys($this->dependencies)),
                $class
            ));
        }

        $this->dependencies[$class] = true;

        /** @var TService $instance */
        $instance = $this->instantiator->instantiate($class, $arguments);

        if (array_key_exists($class, $this->dependencies)) {
            unset($this->dependencies[$class]);
        }

        return $this->applyExtensions($class, $instance);
    }

    /**
     * @template TArgument
     * @template TResult
     *
     * @param callable(array<TArgument>):TResult $callback
     * @param array<TArgument>                   $arguments
     *
     * @throws Throwable
     *
     * @return TResult
     */
    public function call(callable $callback, array $arguments = []): mixed
    {
        return $callback(
            ...$this->parameterBuilder->build(
                match (true) {
                    is_string($callback) => match (true) {
                        str_contains($callback, '::') => (function (string $callback) {
                            $position = mb_strpos($callback, '::');

                            $class = mb_substr($callback, 0, $position);

                            $method = mb_substr($callback, $position + 2);

                            return $this->reflector->reflectClass($class)
                                ->getMethod($method)
                                ->getParameters();
                        })($callback),
                        default => $this->reflector->reflectFunction($callback)
                            ->getParameters(),
                    },
                    $callback instanceof Closure => $this->reflector->reflectFunction($callback)
                        ->getParameters(),
                    is_array($callback) => match (true) {
                        is_object($callback[0]) => $this->reflector->reflectClass($callback[0]::class)->getMethod(
                            $callback[1]
                        )->getParameters(),
                        default => $this->reflector->reflectClass($callback[0])->getMethod(
                            $callback[1]
                        )->getParameters(),
                    },
                    default => $this->reflector->reflectClass($callback::class)->getMethod('__invoke')->getParameters()
                } ?? [],
                $arguments
            )
        );
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>                     $service
     * @param class-string<ExtensionInterface<TService>> $extension
     */
    public function extend(string $service, string $extension): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! is_a($extension, ExtensionInterface::class, true)) {
            throw new ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException($extension);
        }

        if (array_key_exists($extension, $this->extensions[$service] ?? [])) {
            throw new ServiceExtensionAlreadyRegisteredException($extension);
        }

        $this->extensions[$service][$extension] = $extension;
    }

    /**
     * Provide a FactoryInterface for a service.
     *
     * @template TService of object
     *
     * @param class-string<TService>                   $service
     * @param class-string<FactoryInterface<TService>> $serviceFactory
     */
    public function factory(string $service, string $serviceFactory): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! is_a($serviceFactory, FactoryInterface::class, true)) {
            throw new ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException($serviceFactory);
        }

        $this->factories[$service] = static fn (
            ContainerInterface $container
        ): object => $container->invoke($serviceFactory);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ExceptionInterface
     * @throws Throwable
     * @throws NotFoundExceptionInterface
     *
     * @return TService
     */
    public function get(string $service): object
    {
        $class = $this->resolve($service);

        if (is_a($class, ContainerInterface::class, true)) {
            return $this;
        }

        if (array_key_exists($class, $this->instances)) {
            return $this->instances[$class];
        }

        if (array_key_exists($class, $this->factories)) {
            $instance = $this->call($this->factories[$class]);

            if (! is_object($instance)) {
                throw new ServiceMustBeAnObjectException($service);
            }

            return $this->applyExtensions($class, $instance);
        }

        return match (true) {
            class_exists($class, true) => $this->build($class),
            default => throw new ServiceNotFoundException($class),
        };
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     */
    public function has(string $service): bool
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        return match (true) {
            default => array_reduce(
                $this->bindings,
                /**
                 * @param array<class-string<TService>> $binding
                 */
                static fn (bool $carry, array $binding): bool => $carry
                    || in_array($service, $binding, true),
                false
            ),
            array_key_exists($service, $this->instances),
            array_key_exists($service, $this->factories),
            array_key_exists($service, $this->services),
            array_key_exists($service, $this->aliases),
            is_a($service, ContainerInterface::class, true) => true,
        };
    }

    /**
     * @template TInvokable of object
     * @template TArgument
     * @template TResult
     *
     * @param callable-string|class-string<TInvokable> $invokable
     * @param array<TArgument>                         $arguments
     *
     * @throws Throwable
     *
     * @return TResult
     */
    public function invoke(string $invokable, array $arguments = []): mixed
    {
        /** @var callable(array<TArgument>):TResult $callable */
        $callable = $this->get($invokable);

        return $this->call($callable, $arguments);
    }

    /**
     * @param class-string<ServiceProviderInterface> $serviceProvider
     *
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException
     * @throws Throwable
     */
    public function provide(string $serviceProvider): void
    {
        if (! is_a($serviceProvider, ServiceProviderInterface::class, true)) {
            throw new ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException($serviceProvider);
        }

        if (array_key_exists($serviceProvider, $this->providers)) {
            throw new ServiceProviderAlreadyRegisteredException($serviceProvider);
        }

        $this->providers[$serviceProvider] = true;

        $this->invoke($serviceProvider);
    }

    public function register(string $abstract, string $concrete = null, array $tags = []): void
    {
        $concrete ??= $abstract;

        if (trim($abstract) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (trim($concrete) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($tags !== []) {
            $this->tag($abstract, $tags);
        }

        $this->services[$concrete] ??= $concrete;

        if ($abstract === $concrete) {
            return;
        }

        $this->aliases[$abstract] = $concrete;
    }

    public function remove(string $service): void
    {
        unset(
            $this->aliases[$service],
            $this->extensions[$service],
            $this->factories[$service],
            $this->instances[$service],
            $this->services[$service],
            $this->tags[$service],
        );
    }

    /**
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceTagMustBeNonEmptyStringException
     * @throws ExceptionInterface
     */
    public function set(string $service, callable|object $value, array $tags = []): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($tags !== []) {
            $this->tag($service, $tags);
        }

        if (is_callable($value)) {
            $this->factories[$service] = $value;
            return;
        }

        $this->instances[$service] = $value;
    }

    /**
     * @throws ExceptionInterface
     */
    public function tag(string $service, array $tags): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        foreach ($tags as $tag) {
            if (trim($tag) === '') {
                throw new ServiceTagMustBeNonEmptyStringException();
            }

            $this->tags[$tag][$service] = $service;
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $tag
     *
     * @throws Throwable
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return Generator<TService>
     */
    public function tagged(string $tag): Generator
    {
        if (trim($tag) === '') {
            throw new ServiceTagMustBeNonEmptyStringException();
        }

        if (! array_key_exists($tag, $this->tags)) {
            throw new ServiceTagNotFoundException($tag);
        }

        /** @var class-string<TService> $service */
        foreach ($this->tags[$tag] as $service) {
            yield $this->get($service);
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     * @param list<string>           $tags
     */
    public function untag(string $service, array $tags): void
    {
        foreach ($tags as $tag) {
            if (! array_key_exists($tag, $this->tags)) {
                throw new ServiceTagNotFoundException($tag);
            }

            if (! array_key_exists($service, $this->tags[$tag])) {
                throw new ServiceNotFoundException($tag);
            }

            unset($this->tags[$tag][$service]);
        }
    }

    private function applyExtensions(string $service, object $instance): object
    {
        $this->instances[$service] = $instance;

        $extensions = $this->extensions ?? [];
        if ($extensions === []) {
            return $instance;
        }

        foreach (array_keys($extensions) as $serviceName) {
            if (! $instance instanceof $serviceName) {
                continue;
            }

            foreach ($extensions[$serviceName] ?? [] as $extension) {
                $instance = $this->invoke($extension, [$this, $instance]);
            }
        }

        return $this->instances[$service] = $instance;
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     */
    private function resolve(string $service): string
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        while (array_key_exists($service, $this->aliases)) {
            $service = $this->aliases[$service];
        }

        $bindings = $this->bindings ?? [];

        $dependencies = $this->dependencies ?? [];

        if ($bindings === [] || $dependencies === []) {
            return $service;
        }

        return $bindings[array_key_last($dependencies)][$service] ?? $service;
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }
}
