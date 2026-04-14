<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

use Ghostwriter\Container\Interface\Exception\ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;

interface BuilderInterface
{
    /**
     * Provide an alternative name for a service.
     *
     * @template TService of object
     * @template TAlias of object
     *
     * @param class-string<TService> $alias
     * @param class-string<TAlias>   $service
     *
     * @throws ContainerNotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function alias(string $alias, string $service): void;

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
     * @template TService of object
     *
     * @param class-string<TService> $service
     * @param TService               $instance
     */
    public function set(string $service, object $instance): void;

    /**
     * Remove a service from the container.
     *
     * @template TService of object
     *
     * @param class-string<TService> $service
     */
    public function unset(string $service): void;
}
