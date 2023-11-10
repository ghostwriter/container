<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

use Generator;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use ReflectionException;
use Throwable;

/**
 * An extendable, closure based dependency injection container.
 */
interface ContainerInterface
{
    /**
     * Provide an alternative name for a registered service.
     *
     * @template TAbstract of object
     * @template TConcrete of object
     *
     * @param class-string<TAbstract> $name
     * @param class-string<TConcrete> $service
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function alias(string $name, string $service): void;

    /**
     * Provide contextual binding for the given concrete class.
     *
     * while building $concrete, when $abstract is requested, $implementation will be used instead.
     *
     * $container->bind(GitHub::class, ClientInterface::class, GitHubClient::class);
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function bind(string $concrete, string $abstract, string $implementation): void;

    /**
     * Create an object using the given Container to resolve dependencies.
     *
     * @template TArgument
     * @template TService of object
     *
     * @param class-string<TService> $service
     * @param array<TArgument>       $arguments optional constructor arguments passed to build the new class instance
     *
     * @throws NotFoundExceptionInterface  if no entry was found for **this** identifier
     * @throws ContainerExceptionInterface if there is an error while retrieving the entry
     *
     * @return TService
     */
    public function build(string $service, array $arguments = []): object;

    /**
     * Invoke the $callback with optional arguments.
     *
     * @template TArgument
     * @template TArguments of array<TArgument>
     * @template TReturn
     * @template TInvokable of callable|array{0:object,1:string}
     *
     * @param callable(TArguments):TReturn $callback
     * @param TArguments                   $arguments optional arguments passed to $callback
     *
     * @throws ReflectionException
     * @throws Throwable
     *
     * @return TReturn
     */
    public function call(callable $callback, array $arguments = []): mixed;

    /**
     * "Extend" a service object in the container.
     *
     * @template TService of object
     *
     * @param class-string<TService>                     $service
     * @param class-string<ExtensionInterface<TService>> $extension
     */
    public function extend(string $service, string $extension): void;

    /**
     * Instantiate and return the service with the given id.
     *
     * Note: This method will return the same instance on subsequent calls.
     *        Use build() to create a new instance.
     *
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws NotFoundExceptionInterface  if no entry was found for the given identifier
     * @throws ContainerExceptionInterface If error while retrieving the entry
     *
     * @return TService
     */
    public function get(string $service): object;

    /**
     * Determine if a $service exists in the Container.
     *
     * @param non-empty-string $service
     */
    public function has(string $service): bool;

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
    public function provide(string $serviceProvider): void;

    /**
     * Bind abstract classes or interfaces to concrete implementations.
     *
     * @template TAbstract of object
     * @template TConcrete of object
     *
     * @param class-string<TAbstract> $abstract
     * @param class-string<TConcrete> $concrete
     * @param array<class-string>     $tags
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function register(string $abstract, string $concrete = null, array $tags = []): void;

    /**
     * Remove a service from the container.
     *
     * @template TService of object
     *
     * @param class-string<TService> $service
     */
    public function remove(string $service): void;

    /**
     * Assigns a service on the given container.
     *
     * @template TService of callable|object
     *
     * @param TService      $value
     * @param array<string> $tags
     */
    public function set(string $service, callable|object $value, array $tags = []): void;

    /**
     * Assign a set of tags to a given service id.
     *
     * @template TService of object
     *
     * @param class-string<TService>              $service
     * @param list<class-string|non-empty-string> $tags
     */
    public function tag(string $service, array $tags): void;

    /**
     * Resolve services with the given tag.
     *
     * @template TService of object
     *
     * @param class-string<TService> $tag
     *
     * @return Generator<TService>
     */
    public function tagged(string $tag): Generator;

    /**
     * Remove a set of tags to a given service id.
     *
     * @template TService of object
     *
     * @param class-string<TService>              $service
     * @param list<class-string|non-empty-string> $tags
     */
    public function untag(string $service, array $tags): void;
}