<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

use Ghostwriter\Container\Interface\Exception\ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Throwable;

/**
 * An extendable, closure based dependency injection container.
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Provide an alternative name for a service.
     *
     * @template TService of object
     * @template TAlias of object
     *
     * @param class-string<TService> $id
     * @param class-string<TAlias>   $alias
     *
     * @throws ContainerNotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function alias(string $id, string $alias): void;

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
     * @param class-string<TBindAbstract>       $abstract
     * @param class-string<TBindImplementation> $implementation
     *
     * @throws ContainerNotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function bind(string $concrete, string $abstract, string $implementation): void;

    /**
     * Create an object using the given Container to resolve dependencies.
     *
     * @template TBuild of object
     * @template TArgument
     *
     * @param class-string<TBuild> $id
     * @param list<TArgument>      $arguments optional constructor arguments passed to build the new class instance
     *
     * @throws ContainerExceptionInterface         if there is an error while retrieving the entry
     * @throws ContainerNotFoundExceptionInterface if no entry was found for **this** identifier
     *
     * @return TBuild
     */
    public function build(string $id, array $arguments = []): object;

    /**
     * Invoke a callable string with optional arguments.
     *
     * @template TInvoke of object
     * @template TArgument
     * @template TResult
     *
     * @param (array{0:(class-string<TInvoke>|TInvoke),1:'__invoke'|string}|callable|callable-string|(Closure(TArgument...):TResult)|TInvoke) $callable
     * @param array<non-empty-string,TArgument>                                                                                               $arguments
     *
     * @throws Throwable
     *
     * @return TResult
     *
     */
    public function call(callable|string $callable, array $arguments = []): mixed;

    /**
     * @param class-string<DefinitionInterface> $definition
     *
     * @throws ContainerExceptionInterface
     * @throws ContainerNotFoundExceptionInterface
     */
    public function define(string $definition): void;

    /**
     * "Extend" a service object in the container.
     *
     * @template TService of object
     *
     * @param class-string<TService>                     $id
     * @param class-string<ExtensionInterface<TService>> $extension
     */
    public function extend(string $id, string $extension): void;

    /**
     * Provide a FactoryInterface for a service.
     *
     * @template TService of object
     *
     * @param class-string<TService>                   $id
     * @param class-string<FactoryInterface<TService>> $factory
     */
    public function factory(string $id, string $factory): void;

    /**
     * Instantiate and return the service with the given id.
     *
     * Returns the same instance on subsequent calls, Use `$container->build()` to create a new instance.
     *
     * @template TService of object
     *
     * @param class-string<TService> $id
     *
     * @throws ContainerExceptionInterface         If error while retrieving the entry
     * @throws ContainerNotFoundExceptionInterface if no entry was found for the given identifier
     *
     * @return TService
     */
    public function get(string $id): object;

    /**
     * Determine if a $id exists in the Container.
     *
     * @template TService of object
     *
     * @param class-string<TService> $id
     *
     * @psalm-assert-if-true class-string<TService> $id
     */
    public function has(string $id): bool;

    public function reset(): void;

    /**
     * @template TService of object
     *
     * @param class-string<TService> $id
     * @param TService               $value
     */
    public function set(string $id, object $value): void;

    /**
     * Remove a service from the container.
     *
     * @template TService of object
     *
     * @param class-string<TService> $id
     */
    public function unset(string $id): void;
}
