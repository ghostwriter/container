<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Contract;

use ArrayAccess;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Exception\InvalidArgumentException;
use Ghostwriter\Container\Exception\NotInstantiableException;
use Psr\Container\ContainerExceptionInterface as PsrContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface as PsrNotFoundExceptionInterface;

/**
 * An extendable, closure based dependency injection container.
 */
interface ContainerInterface extends ArrayAccess, PsrContainerInterface
{
    public const ALIASES = 'aliases';

    public const DEPENDENCIES = 'dependencies';

    public const EXTENSIONS = 'extensions';

    public const FACTORIES = 'factories';

    public const PROVIDERS = 'providers';

    public const SERVICES = 'services';

    public const TAGS = 'tags';

    /**
     * Provides an alternative name for a registered service.
     *
     * Should map one alias to a service id, or another alias (aliases are recursively resolved)
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function alias(string $alias, string $id): void;

    /**
     * Binds abstract classes and interfaces to concrete implementations
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function bind(string $abstract, ?string $concrete = null): void;

    /**
     * Given a fully qualified class name, it will return a new instance of that class.
     *
     * @param class-string $class
     * @param array<string,mixed> $arguments Optional constructor arguments to use build the new class instance.
     * @throws InvalidArgumentException The argument keys MUST be of type non-empty string.
     * @throws NotInstantiableException If the class name is not instantiable; (is an interface or an abstract class).
     * @throws PsrNotFoundExceptionInterface No entry or class found for the given name.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function build(string $class, array $arguments = []): object;

    /**
     * "Extend" a service object in the container.
     *
     * Callback runs after creating an object, using a registered factory with the given Service ID.
     *
     * @param callable(ContainerInterface,object):object $extension
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function extend(string $class, callable $extension): void;

    /**
     * @throws InvalidArgumentException Invalid identifier.
     * @throws PsrNotFoundExceptionInterface No entry was found for **this** identifier.
     * @throws PsrContainerExceptionInterface Error while retrieving the entry.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get(string $id): mixed;

    public function has(string $id): bool;

    /**
     * @throws ContainerExceptionInterface
     */
    public function register(ServiceProviderInterface $serviceProvider): void;

    /**
     * Remove a registered service.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function remove(string $id): void;

    /**
     * Assigns a service on the given container.
     *
     * @param iterable<string> $tags
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function set(string $id, mixed $value, iterable $tags = []): void;

    /**
     * Assign a set of tags to a given service id.
     *
     * @param iterable<string> $tags
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function tag(string $id, iterable $tags): void;

    /**
     * Resolve services for a given tag.
     *
     * @return iterable<string>
     */
    public function tagged(string $tag): iterable;
}
