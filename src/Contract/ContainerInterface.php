<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Contract;

use Closure;
use Generator;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use ReflectionException;
use Throwable;

/**
 * An extendable, closure based dependency injection container.
 */
interface ContainerInterface
{
    /**
     * Remove all registered services from this container and reset the default services.
     */
    public function __destruct();

    /**
     * @throws ContainerExceptionInterface if "__clone()" method is called
     */
    public function __clone();

    /**
     * @throws ContainerExceptionInterface if "__serialize()" method is called
     */
    public function __serialize(): array;

    /**
     * @throws ContainerExceptionInterface if "__unserialize()" method is called
     */
    public function __unserialize(array $data): void;

    /**
     * Provide an alternative name for a registered service.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function alias(string $abstract, string $concrete): void;

    /**
     * Bind abstract classes or interfaces to concrete implementations.
     *
     * @param array<string> $tags
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function bind(string $abstract, ?string $concrete = null, array $tags = []): void;

    /**
     * Create an object using the given Container to resolve dependencies.
     *
     * @template TObject of object
     *
     * @param class-string<TObject> $class the class name
     * @param array<string,mixed> $arguments optional constructor arguments passed to build the new class instance
     *
     * @throws NotFoundExceptionInterface if no entry was found for **this** identifier
     * @throws ContainerExceptionInterface if there is an error while retrieving the entry
     *
     * @return TObject
     */
    public function build(string $class, array $arguments = []): object;

    /**
     * Call an invokable class or closure with optional arguments.
     *
     * @template TValue
     * @template TReturn
     *
     * @param callable(array<string,TValue>):class-string<Closure(array<string,TValue>):TReturn>|TReturn $invokable
     * @param array<string,TValue> $arguments optional arguments passed to $callback
     *
     * @throws ReflectionException
     * @throws Throwable
     *
     * @return TReturn
     */
    public function call(callable|string $invokable, array $arguments = []): mixed;

    /**
     * "Extend" a service object in the container.
     *
     * @template TObject of object
     *
     * @param class-string<TObject> $class the class name
     * @param callable(self,TObject):TObject $extension the callable
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function extend(string $class, callable $extension): void;

    /**
     * Instantiate and return the service with the given id.
     *
     * Note: This method will return the same instance on subsequent calls.
     *
     * @template TService
     * @template TObject of object
     *
     * @param class-string<TObject>|string $id
     *
     * @throws NotFoundExceptionInterface if no entry was found for the given identifier
     * @throws ContainerExceptionInterface If error while retrieving the entry
     *
     * @return ($id is class-string ? TObject : TService)
     */
    public function get(string $id): mixed;

    /**
     * Instantiate and return the Container.
     */
    public static function getInstance(): self;

    /**
     * Determine if a service $id exists in the Container.
     */
    public function has(string $id): bool;

    /**
     * Register a ServiceProvider class.
     *
     * Note: Service providers are automatically registered via `build` or `get` method.
     *
     * @param class-string<ServiceProviderInterface> $serviceProvider
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function register(string $serviceProvider): void;

    /**
     * Remove a registered service.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function remove(string $id): void;

    /**
     * Reassigns a service on the given container.
     *
     * @template TService
     *
     * @param TService $value
     * @param array<string> $tags
     */
    public function replace(string $id, mixed $value, array $tags = []): void;

    /**
     * Resolves an alias to the service id.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve(string $id): string;

    /**
     * Assigns a service on the given container.
     *
     * @template TService
     *
     * @param TService $value
     * @param array<string> $tags
     */
    public function set(string $id, mixed $value, array $tags = []): void;

    /**
     * Assign a set of tags to a given service id.
     *
     * @param array<string> $tags
     */
    public function tag(string $id, array $tags): void;

    /**
     * Resolve services for a given tag.
     *
     * @template TObject of object
     *
     * @param class-string<TObject> $tag
     *
     * @return Generator<TObject>
     */
    public function tagged(string $tag): Generator;
}
