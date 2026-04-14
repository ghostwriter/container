<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

use Deprecated;
use Ghostwriter\Container\Interface\Exception\ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Throwable;

/**
 * An extendable, closure based dependency injection container.
 */
interface ContainerInterface extends BuilderInterface
{
    /**
     * Create an object using the given Container to resolve dependencies.
     *
     * Returns a new instance on each call, Use `$container->get()` to return the same instance on subsequent calls.
     *
     * @template TBuild of object
     * @template TArgument
     *
     * @param class-string<TBuild> $service
     * @param list<TArgument>      $arguments optional constructor arguments passed to build the new class instance
     *
     * @throws ContainerExceptionInterface         if there is an error while retrieving the entry
     * @throws ContainerNotFoundExceptionInterface if no entry was found for **this** identifier
     *
     * @return TBuild
     */
    public function build(string $service, array $arguments = []): object;

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
     *
     * @param class-string<DefinitionInterface> $definition
     *
     * @throws ContainerExceptionInterface
     * @throws ContainerNotFoundExceptionInterface
     */
    #[Deprecated(
        message: 'Use `ProviderInterface` with a composer extra definition instead, will be removed in v7.0.0.'
    )]
    public function define(string $definition): void;

    /**
     * Instantiate and return the service with the given id.
     *
     * Returns the same instance on subsequent calls, Use `$container->build()` to create a new instance.
     *
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ContainerExceptionInterface         If error while retrieving the entry
     * @throws ContainerNotFoundExceptionInterface if no entry was found for the given identifier
     *
     * @return TService
     */
    public function get(string $service): object;

    /**
     * Determine if the $service exists in the Container.
     *
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @psalm-assert-if-true class-string<TService> $service
     */
    public function has(string $service): bool;

    public function reset(): void;
}
