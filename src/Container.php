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
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use InvalidArgumentException;
use Throwable;

use function array_key_exists;
use function is_callable;

/**
 * @see \Ghostwriter\Container\Tests\Unit\ContainerTest
 */
final class Container implements ContainerInterface
{
    /**
     * @template TService of object
     *
     * @var array<class-string<TService>, class-string<TService>>
     */
    private array $aliases = [
        ContainerInterface::class => self::class,
    ];

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
     * @var array<class-string<TService>,Closure(self,TService):TService>
     */
    private array $factories = [];

    private static self $instance;

    /**
     * @template TService of object
     *
     * @var array<class-string<TService>, TService>
     */
    private array $instances = [];

    private array $providers = [];

    /**
     * @template TService of object
     *
     * @var array<class-string<TService>,list<string>>
     */
    private array $tags = [];

    private function __construct(
        private readonly Instantiator $instantiator = new Instantiator(),
    ) {
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
        $this->dependencies = [];
        $this->factories = [];
        $this->instances = [];
        $this->providers = [];
        $this->tags = [];
    }

    /**
     * @throws ContainerExceptionInterface if "__clone()" method is called
     *
     * @return never
     */
    public function __clone(): void
    {
        throw new DontCloneContainerException();
    }

    /**
     * @throws ContainerExceptionInterface if "__serialize()" method is called
     *
     * @return never
     */
    public function __serialize(): array
    {
        throw new DontSerializeContainerException();
    }

    /**
     * @param array<string,mixed> $data
     *
     * @throws ContainerExceptionInterface if "__unserialize()" method is called
     *
     * @return never
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
    public function bind(
        string $concrete,
        string $abstract,
        string $implementation
    ): void {
        if (trim($concrete) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($concrete)) {
            throw new ServiceNotFoundException($concrete);
        }

        if (trim($abstract) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (
            ! class_exists($abstract) &&
            ! interface_exists($abstract)
        ) {
            throw new ServiceNotFoundException($abstract);
        }

        if (trim($implementation) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (
            ! class_exists($implementation) &&
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
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws Throwable
     *
     * @return TService
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

        $this->dependencies[$service] = true;

        /** @var TService $object */
        $object = $this->instantiator->instantiate(
            $this,
            $service,
            $arguments
        );

        if (array_key_exists($service, $this->dependencies)) {
            unset($this->dependencies[$service]);
        }

        return $this->apply($service, $object);
    }

    /**
     * @template TArgument
     * @template TResult
     *
     * @param callable(array<TArgument>):TResult|callable-string $callback
     */
    public function call(callable $callback, array $arguments = []): mixed
    {
        $parameters = $this->instantiator
            ->buildParameters($this, $callback(...), $arguments);

        return $callback(...$parameters);
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

        if (
            ! is_a($extension, ExtensionInterface::class, true)
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
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws Throwable
     *
     * @return TService
     */
    public function get(string $service): object
    {
        $class = $this->resolve($service);

        if (array_key_exists($class, $this->instances)) {
            return $this->instances[$class];
        }

        if (array_key_exists($class, $this->factories)) {
            return $this->apply($class, $this->call($this->factories[$class]));
        }

        if (is_a($class, ContainerInterface::class, true)) {
            return $this;
        }

        if (! class_exists($class)) {
            throw new ServiceNotFoundException($class);
        }

        return $this->apply($class, $this->build($class));
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function has(string $service): bool
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        return array_key_exists($service, $this->instances) ||
            array_key_exists($service, $this->factories) ||
            array_key_exists($service, $this->aliases) ||
            is_a($service, ContainerInterface::class, true) ||
            array_reduce(
                $this->bindings,
                static fn (
                    bool $carry,
                    array $binding
                ): bool => $carry || in_array($service, $binding, true),
                false
            );
    }

    /**
     * @template TInvokable of object
     * @template TArgument
     * @template TResult
     *
     * @param callable-string&class-string<TInvokable> $invokable
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
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException
     * @throws ContainerExceptionInterface
     */
    public function provide(string $serviceProvider): void
    {
        if (
            ! is_a($serviceProvider, ServiceProviderInterface::class, true)
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

        $this->factories[$concrete] ??= static fn (
            ContainerInterface $container
        ): object => $container->build($concrete);

        if ($tags !== []) {
            $this->tag($abstract, $tags);
        }
    }

    public function remove(string $service): void
    {
        unset(
            $this->extensions[$service],
            $this->factories[$service],
            $this->instances[$service],
            $this->tags[$service],
            $this->aliases[$service],
        );
    }

    /**
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceTagMustBeNonEmptyStringException
     * @throws ContainerExceptionInterface
     */
    public function set(string $service, callable|object $value, array $tags = []): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($tags !== []) {
            $this->tag($service, $tags);
        }

        $this->instances[$service] = null;

        unset($this->instances[$service]);

        $this->factories[$service] = ! is_callable($value)
                ? static fn (ContainerInterface $container): object => $value
            : $value;
    }

    /**
     * @throws ContainerExceptionInterface
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
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

    public function untag(string $service, array $tags): void
    {
        foreach ($tags as $tag) {
            if (! array_key_exists($tag, $this->tags)) {
                continue;
            }

            if (! array_key_exists($service, $this->tags[$tag])) {
                continue;
            }

            unset($this->tags[$tag][$service]);
        }
    }

    private function apply(string $service, object $object): object
    {
        $this->instances[$service] = $object;

        foreach ($this->extensions[$service] ?? [] as $extension) {
            $object = $this->invoke($extension, [$this, $object]);
        }

        return $this->instances[$service] = $object;
    }

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
}
