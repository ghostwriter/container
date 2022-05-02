<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Contract;

use ArrayAccess;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Exception\BadMethodCallException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\InvalidArgumentException;
use Ghostwriter\Container\Exception\LogicException;
use Ghostwriter\Container\Exception\NotFoundException;
use Ghostwriter\Container\Exception\NotInstantiableException;
use Psr\Container\ContainerExceptionInterface as PsrContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface as PsrNotFoundExceptionInterface;

/**
 * An extendable, closure based dependency injection container.
 */
interface ContainerInterface extends ArrayAccess, PsrContainerInterface
{
    /**
     * @var string
     */
    public const ALIASES = 'aliases';

    public const DEFAULT_SERVICES = [
        self::ALIASES      => [
            self::class    => Container::class,
            PsrContainerInterface::class => Container::class,
        ],
        self::DEPENDENCIES => [],
        self::EXTENSIONS   => [
            Container::class => [],
        ],
        self::FACTORIES    => [],
        self::PROVIDERS    => [],
        self::SERVICES     => [
            Container::class => null,
        ],
        self::TAGS         => [],
    ];

    /**
     * @var string
     */
    public const DEPENDENCIES = 'dependencies';

    /**
     * @var string
     */
    public const EXTENSIONS = 'extensions';

    /**
     * @var string
     */
    public const FACTORIES = 'factories';

    /**
     * @var string
     */
    public const PROVIDERS = 'providers';

    /**
     * @var string
     */
    public const SERVICES = 'services';

    /**
     * @var string
     */
    public const TAGS = 'tags';

    /**
     * Destroy the "static" instance of this container.
     */
    public function __destruct();

    /**
     * @throws BadMethodCallException if "__clone()" method is called
     *
     * @return never
     */
    public function __clone();

    /**
     * @throws BadMethodCallException if "__serialize()" method is called
     *
     * @return never
     */
    public function __serialize(): array;

    /**
     * @throws BadMethodCallException if "__unserialize()" method is called
     *
     * @return never
     */
    public function __unserialize(array $data): void;

    /**
     * Add a service extension.
     *
     * @throws LogicException if $extension is already registered
     */
    public function add(string $id, ExtensionInterface $extension): void;

    /**
     * Provide an alternative name for a registered service.
     *
     * @throws LogicException           if $alias and $id are the same
     * @throws InvalidArgumentException if $alias or $id is empty
     * @throws NotFoundException        if $id has not been registered
     */
    public function alias(string $alias, string $id): void;

    /**
     * Bind abstract classes or interfaces to concrete implementations.
     *
     * @throws InvalidArgumentException if $abstract is empty
     * @throws LogicException           if $abstract is already registered
     */
    public function bind(string $abstract, ?string $concrete = null): void;

    /**
     * Create an object using the given Container to resolve dependencies.
     *
     * @template T of object
     *
     * @param class-string<T>|string $class     the class name
     * @param array<string,mixed>    $arguments optional constructor arguments passed to build the new class instance
     *
     * @throws CircularDependencyException if a circular dependency is detected
     * @throws NotInstantiableException    if $class is not instantiable; (is an interface or an abstract class)
     *
     * @return T
     */
    public function build(string $class, array $arguments = []): object;

    /**
     * "Extend" a service object in the container.
     *
     * @param callable(self,object):void $extension
     *
     * @throws InvalidArgumentException if $class is empty
     */
    public function extend(string $class, callable $extension): void;

    /**
     * Instantiate and return the service with the given id.
     *
     * Note: This method will return the same instance on subsequent calls.
     *
     * @template T of mixed
     * @template TObject of object
     *
     * @param class-string<T>|string $id
     *
     * @throws InvalidArgumentException                                   if $id is empty
     * @throws NotFoundException                                          if $id is not registered
     * @throws CircularDependencyException                                if a circular dependency is detected
     * @throws NotInstantiableException                                   if $class is not instantiable; (is an interface or an abstract class)
     * @throws NotFoundExceptionInterface|PsrNotFoundExceptionInterface   if no entry was found for **this** identifier
     * @throws ContainerExceptionInterface|PsrContainerExceptionInterface If error while retrieving the entry
     *
     * @return T|TObject
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

    /** @param string $offset */
    public function offsetExists(mixed $offset): bool;

    /** @param string $offset */
    public function offsetGet(mixed $offset): mixed;

    /** @param string $offset */
    public function offsetSet(mixed $offset, mixed $value): void;

    /** @param string $offset */
    public function offsetUnset(mixed $offset): void;

    /**
     * Register a service provider.
     *
     * Note: Service providers are automatically registered via `build` or `get` method.
     *
     * @throws LogicException if $serviceProvider is already registered
     */
    public function register(ServiceProviderInterface $serviceProvider): void;

    /**
     * Remove a registered service.
     *
     * @throws InvalidArgumentException if the service $id is empty
     * @throws NotFoundException        if the service $id is not registered
     */
    public function remove(string $id): void;

    /**
     * Resolves an alias to the service id.
     *
     * @throws InvalidArgumentException if the service $id is empty
     */
    public function resolve(string $id): string;

    /**
     * Assigns a service on the given container.
     *
     * @template T
     *
     * @param T                $value
     * @param iterable<string> $tags
     *
     * @throws InvalidArgumentException if the service $id is empty
     * @throws LogicException           if the service $id is already registered
     */
    public function set(string $id, mixed $value, iterable $tags = []): void;

    /**
     * Assign a set of tags to a given service id.
     *
     * @param iterable<string> $tags
     *
     * @throws InvalidArgumentException if the service $id or a service tag in $tags is empty
     */
    public function tag(string $id, iterable $tags): void;

    /**
     * Resolve services for a given tag.
     *
     * @return iterable<string>
     */
    public function tagged(string $tag): iterable;
}
