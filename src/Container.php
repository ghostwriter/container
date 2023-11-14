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
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use InvalidArgumentException;
use Throwable;
use function array_key_exists;
use function in_array;
use function is_callable;
use function is_object;

/**
 * @see \Ghostwriter\Container\Tests\Unit\ContainerTest
 */
final class Container implements ContainerInterface
{
    private static self $instance;
    /**
     * @template TService of object
     *
     * @var array<class-string<TService>, class-string<TService>>
     */
    private array $aliases = [
        ContainerInterface::class => self::class,
    ];
    /**
     * @template TService of object
     *
     * @var array<class-string<TService>, class-string<TService>>
     */
    private array $bindings = [];
    private array $dependencies = [];
    /**
     * @template TService of object
     *
     * @var array<class-string<TService>,list<ExtensionInterface<TService>>>
     */
    private array $extensions = [];
    /**
     * @template TService of object
     *
     * @var array<class-string<TService>|callable-string>
     */
    private array $factories = [];
    /**
     * @template TService of object
     *
     * @var array<class-string<TService>|callable-string>
     */
    private array $services = [];
    /**
     * @template TService of object
     *
     * @var array<class-string<TService>, TService>
     */
    private array $instances = [];

    /**
     * @var array<class-string<ServiceProviderInterface>, null>
     */
    private array $providers = [];

    /**
     * @template TService of object
     *
     * @var array<class-string<TService>,list<string>>
     */
    private array $tags = [];

    private function __construct(
        private readonly Instantiator $instantiator = new Instantiator(),
    )
    {
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Remove all registered services from this container and reset the default services.
     */
    public function __destruct()
    {
        $this->aliases = [
            ContainerInterface::class => self::class,
        ];
        $this->extensions = [];
        $this->bindings = [];
        $this->factories = [];
        $this->dependencies = [];
        $this->services = [];
        $this->instances = [];
        $this->providers = [];
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
     * @param class-string<TConcreteClass> $concrete
     * @param class-string<TAbstractClass> $abstract
     * @param class-string<TImplementationClass> $implementation
     */
    public function bind(
        string $concrete,
        string $abstract,
        string $implementation
    ): void
    {
        if (trim($concrete) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (!class_exists($concrete)) {
            throw new ServiceNotFoundException($concrete);
        }

        if (trim($abstract) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (
            !class_exists($abstract) &&
            !interface_exists($abstract)
        ) {
            throw new ServiceNotFoundException($abstract);
        }

        if (trim($implementation) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (
            !class_exists($implementation) &&
            !interface_exists($implementation)
        ) {
            throw new ServiceNotFoundException($implementation);
        }

        $this->bindings[$concrete][$abstract] = $implementation;
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     * @param class-string<ExtensionInterface<TService>> $extension
     */
    public function extend(string $service, string $extension): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (
            !is_a($extension, ExtensionInterface::class, true)
            || $extension === ExtensionInterface::class
        ) {
            throw new ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException($extension);
        }

        if (array_key_exists($extension, $this->extensions[$service] ?? [])) {
            throw new ServiceExtensionAlreadyRegisteredException($extension);
        }

        $this->extensions[$service][$extension] = $extension;
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
                static fn(bool $carry, array $binding): bool => $carry
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
     * @param class-string<ServiceProviderInterface> $serviceProvider
     *
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException
     * @throws Throwable
     */
    public function provide(string $serviceProvider): void
    {
        if (
            !is_a($serviceProvider, ServiceProviderInterface::class, true)
            || $serviceProvider === ServiceProviderInterface::class
        ) {
            throw new ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException($serviceProvider);
        }

        if (array_key_exists($serviceProvider, $this->providers)) {
            throw new ServiceProviderAlreadyRegisteredException($serviceProvider);
        }

        $this->providers[$serviceProvider] = null;

        $this->invoke($serviceProvider);
    }

    /**
     * @template TInvokable of object
     * @template TArgument
     * @template TResult
     *
     * @param callable-string|class-string<TInvokable> $invokable
     * @param array<TArgument> $arguments
     *
     * @throws Throwable
     */
    public function invoke(string $invokable, array $arguments = []): mixed
    {
        /** @var callable(array<TArgument>):TResult $callable */
        $callable = $this->get($invokable);

        return $this->call($callable, $arguments);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @return TService
     *
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get(string $service): object
    {
        $class = $this->resolve($service);

        return match (true) {
            array_key_exists($class, $this->instances) => $this->instances[$class],
            is_a($class, ContainerInterface::class, true) => $this,
            !class_exists($class) => throw new ServiceNotFoundException($class),
            default => $this->build($class),
        };
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

    /**
     * @template TArgument
     * @template TResult
     *
     * @param callable(array<TArgument>):TResult $callback
     * @param array<TArgument> $arguments
     *
     * @return TResult
     */
    public function call(callable $callback, array $arguments = []): mixed
    {
        $parameters = $this->instantiator
            ->buildParameters($this, $callback(...), $arguments);

        return $callback(...$parameters);
    }

    /**
     * @template TArgument
     * @template TService of object
     *
     * @param class-string<TService> $service
     * @param array<TArgument> $arguments
     *
     * @return TService
     *
     * @throws CircularDependencyException
     * @throws ClassNotInstantiableException
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws Throwable
     */
    public function build(string $service, array $arguments = []): object
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (is_a($service, ContainerInterface::class, true)) {
            return $this;
        }

        if (array_key_exists($service, $this->dependencies)) {
            throw new CircularDependencyException(sprintf(
                'Class: %s -> %s',
                implode(' -> ', array_keys($this->dependencies)),
                $service
            ));
        }

        $this->dependencies[$service] = null;

        /** @var TService $object */
        $object = match (true) {
            array_key_exists($service, $this->factories) =>
            $this->call($this->factories[$service], $arguments),
            default => $this->instantiator->instantiate($this, $service, $arguments)
        };

        if (array_key_exists($service, $this->dependencies)) {
            unset($this->dependencies[$service]);
        }

        if (!is_object($object)) {
            throw new ServiceMustBeAnObjectException($service);
        }

        $this->instances[$service] = $object;

        foreach ($this->extensions[$service] ?? [] as $extension) {
            $object = $this->invoke($extension, [$this, $object]);
        }

        return $this->instances[$service] = $object;
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

        if ($abstract !== $concrete) {
            $this->aliases[$abstract] = $concrete;
        }

        $this->services[$concrete] ??= $concrete;

        if ($tags !== []) {
            $this->tag($abstract, $tags);
        }
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

        if (is_object($value)) {
            $this->instances[$service] = $value;
            return;
        }

        throw new ServiceMustBeAnObjectException($service);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $tag
     *
     * @return Generator<TService>
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     *
     * @throws ExceptionInterface
     */
    public function tagged(string $tag): Generator
    {
        if (trim($tag) === '') {
            throw new ServiceTagMustBeNonEmptyStringException();
        }

        if (!array_key_exists($tag, $this->tags)) {
            throw new ServiceTagNotFoundException($tag);
        }

        /** @var class-string<TService> $service */
        foreach ($this->tags[$tag] as $service) {
            yield $this->get($service);
        }
    }

    /**
     * @template TService of object
     * @param class-string<TService> $service
     * @param list<string> $tags
     */
    public function untag(string $service, array $tags): void
    {
        foreach ($tags as $tag) {
            if (!array_key_exists($tag, $this->tags)) {
                throw new ServiceTagNotFoundException($tag);
            }

            if (!array_key_exists($service, $this->tags[$tag])) {
                throw new ServiceNotFoundException($tag);
            }

            unset($this->tags[$tag][$service]);
        }
    }
}
