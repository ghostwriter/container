<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

use Generator;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Throwable;

/**
 * An extendable, closure based dependency injection container.
 */
interface ContainerInterface
{
    /**
     * Provide an alternative name for a registered service.
     *
     * @template TService of object
     * @template TAlias of object
     *
     * @param class-string<TService> $service
     * @param class-string<TAlias>   $alias
     *
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     */
    public function alias(string $service, string $alias): void;

    /**
     * Provide contextual binding for the given concrete class.
     *
     * $container->bind(GitHub::class, ClientInterface::class, GitHubClient::class);
     *
     * when building $concrete (GitHub::class),
     * if $abstract (ClientInterface::class) is requested,
     * then $implementation (GitHubClient::class) will be provided.
     *
     * @template TBindConcrete of object
     * @template TBindAbstract of object
     * @template TBindImplementation of object
     *
     * @param class-string<TBindConcrete>       $concrete
     * @param class-string<TBindAbstract>       $service
     * @param class-string<TBindImplementation> $implementation
     *
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     */
    public function bind(string $concrete, string $service, string $implementation): void;

    /**
     * Create an object using the given Container to resolve dependencies.
     *
     * @template TBuild of object
     * @template TArgument
     *
     * @param class-string<TBuild> $service
     * @param list<TArgument>      $arguments optional constructor arguments passed to build the new class instance
     *
     * @throws ExceptionInterface         if there is an error while retrieving the entry
     * @throws NotFoundExceptionInterface if no entry was found for **this** identifier
     *
     * @return TBuild
     *
     */
    public function build(string $service, array $arguments = []): object;

    /**
     * Invoke the $callback with optional arguments.
     *
     * @template TService of object
     * @template TArgument
     * @template TResult
     *
     * @param array{0:(class-string<TService>|TService),1:'__invoke'|string}|callable|callable-string|Closure(TArgument...):TResult|TService $callback
     * @param list<TArgument>                                                                                                                $arguments optional arguments passed to $callback
     *
     * @throws Throwable
     *
     * @return TResult
     *
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
     * @param class-string<FactoryInterface<TService>> $factory
     */
    public function factory(string $service, string $factory): void;

    /**
     * Instantiate and return the service with the given id.
     *
     * Returns the same instance on subsequent calls, Use `$container->build()` to create a new instance.
     *
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ExceptionInterface         If error while retrieving the entry
     * @throws NotFoundExceptionInterface if no entry was found for the given identifier
     *
     * @return TService
     *
     */
    public function get(string $service): object;

    /**
     * Determine if a $service exists in the Container.
     *
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @psalm-assert-if-true class-string<TService> $service
     */
    public function has(string $service): bool;

    /**
     * Invoke a callable string with optional arguments.
     *
     * @template TInvoke of object
     * @template TArgument
     * @template TResult
     *
     * @param class-string<TInvoke> $invokable
     * @param list<TArgument>       $arguments
     *
     * @throws Throwable
     *
     * @return TResult
     *
     */
    public function invoke(string $invokable, array $arguments = []): mixed;

    /**
     * @param class-string<ServiceProviderInterface> $serviceProvider
     *
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(string $serviceProvider): void;

    public function purge(): void;

    /**
     * Bind abstract classes or interfaces to concrete implementations.
     *
     * @template TAbstract of object
     * @template TConcrete of object
     * @template TTag of object
     *
     * @param class-string<TAbstract>  $abstract
     * @param class-string<TConcrete>  $concrete
     * @param list<class-string<TTag>> $tags
     *
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     */
    public function register(string $abstract, ?string $concrete = null, array $tags = []): void;

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
     * @template TService of object
     * @template TTag of object
     *
     * @param class-string<TService>                          $service
     * @param (Closure(ContainerInterface):TService)|TService $value
     * @param list<class-string<TTag>>                        $tags
     */
    public function set(string $service, callable|object $value, array $tags = []): void;

    /**
     * Assign a set of tags to a given service id.
     *
     * @template TService of object
     * @template TTag of object
     *
     * @param class-string<TService>             $service
     * @param non-empty-list<class-string<TTag>> $tags
     */
    public function tag(string $service, array $tags): void;

    /**
     * Resolve services with the given tag.
     *
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
    public function tagged(string $tag): Generator;

    /**
     * Remove a set of tags to a given service id.
     *
     * @template TService of object
     * @template TTag of object
     *
     * @param class-string<TService>             $service
     * @param non-empty-list<class-string<TTag>> $tags
     */
    public function untag(string $service, array $tags): void;
}
