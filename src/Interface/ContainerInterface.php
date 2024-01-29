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
    public const ALIASES = 0;

    public const BINDINGS = 2;

    public const DEPENDENCIES = 4;

    public const EXTENSIONS = 1;

    public const FACTORIES = 3;

    public const INSTANCES = 6;

    public const PROVIDERS = 7;

    public const SERVICES = 5;

    public const TAGS = 8;

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
     * @throws ExceptionInterface
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
     * @throws ExceptionInterface
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
     * @throws ExceptionInterface         if there is an error while retrieving the entry
     * @throws NotFoundExceptionInterface if no entry was found for **this** identifier
     *
     * @return TService
     */
    public function build(string $service, array $arguments = []): object;

    /**
     * Invoke the $callback with optional arguments.
     *
     * @template TArgument
     * @template TReturn
     *
     * @param callable(array<TArgument>):TReturn $callback
     * @param array<TArgument>                   $arguments optional arguments passed to $callback
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
     * Provide a FactoryInterface for a service.
     *
     * @template TService of object
     *
     * @param class-string<TService>                   $service
     * @param class-string<FactoryInterface<TService>> $serviceFactory
     */
    public function factory(string $service, string $serviceFactory): void;

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
     * @throws ExceptionInterface         If error while retrieving the entry
     * @throws NotFoundExceptionInterface if no entry was found for the given identifier
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
    public function invoke(string $invokable, array $arguments = []): mixed;

    /**
     * Register a ServiceProvider class.
     *
     * Note: Service providers are automatically registered via `build` or `get` method.
     *
     * @param class-string<ServiceProviderInterface> $serviceProvider
     *
     * @throws ExceptionInterface
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
     * @throws ExceptionInterface
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
