<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Contract;

use ArrayAccess;
use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\ClassDoseNotExistException;
use Ghostwriter\Container\Exception\DontCloneException;
use Ghostwriter\Container\Exception\DontSerializeException;
use Ghostwriter\Container\Exception\DontUnserializeException;
use Ghostwriter\Container\Exception\NotInstantiableException;
use Ghostwriter\Container\Exception\ServiceAliasMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceCannotAliasItselfException;
use Ghostwriter\Container\Exception\ServiceExtensionAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceIdMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceProviderMustBeSubclassOfServiceProviderInterfaceException;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * An extendable, closure based dependency injection container.
 */
interface ContainerInterface extends ArrayAccess
{
    /**
     * @var string
     */
    public const ALIASES = 'aliases';

    /**
     * @var array{
     *     aliases: array<class-string|string,class-string|string>,
     *     dependencies: array<class-string,bool>,
     *     extensions: array<class-string,callable(ContainerInterface, object):object>,
     *     factories: array<class-string|string,callable(ContainerInterface):object>,
     *     providers: array<class-string,ServiceProviderInterface>,
     *     reflections: array<class-string,ReflectionClass>,
     *     services: array<class-string|string,callable|object|scalar>,
     *     tags: array<class-string|string,array<class-string|string>>,
     * }
     */
    public const DEFAULT_SERVICES = [
        self::ALIASES      => [
            self::class    => Container::class,
        ],
        self::DEPENDENCIES => [],
        self::EXTENSIONS   => [
            Container::class => [],
        ],
        self::FACTORIES    => [],
        self::PROVIDERS    => [],
        self::REFLECTIONS => [],
        self::SERVICES     => [
            Container::class => 0,
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
    public const REFLECTIONS = 'reflections';

    /**
     * @var string
     */
    public const SERVICES = 'services';

    /**
     * @var string
     */
    public const TAGS = 'tags';

    /**
     * Remove all registered services from this container and reset the default services.
     */
    public function __destruct();

    /**
     * @throws DontCloneException if "__clone()" method is called
     */
    public function __clone();

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __get(string $name): mixed;

    public function __isset(string $name): bool;

    /**
     * @throws DontSerializeException if "__serialize()" method is called
     */
    public function __serialize(): array;

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __set(string $name, mixed $value): void;

    /**
     * @throws DontUnserializeException if "__unserialize()" method is called
     */
    public function __unserialize(array $data): void;

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __unset(string $name): void;

    /**
     * Add a service extension.
     *
     * @throws ServiceExtensionAlreadyRegisteredException if $extension is already registered
     */
    public function add(string $id, ExtensionInterface $extension): void;

    /**
     * Provide an alternative name for a registered service.
     *
     * @throws ServiceCannotAliasItselfException         if $alias and $id are the same
     * @throws ServiceAliasMustBeNonEmptyStringException if $alias is empty
     * @throws ServiceIdMustBeNonEmptyStringException    if $id is empty
     * @throws ServiceNotFoundException                  if $id has not been registered
     */
    public function alias(string $id, string $alias): void;

    /**
     * Bind abstract classes or interfaces to concrete implementations.
     *
     * @param iterable<string> $tags
     *
     * @throws ServiceIdMustBeNonEmptyStringException if $concrete is empty
     * @throws ServiceIdMustBeNonEmptyStringException if $abstract is empty
     * @throws ServiceAlreadyRegisteredException      if $abstract is already registered
     */
    public function bind(string $abstract, ?string $concrete = null, iterable $tags = []): void;

    /**
     * Create an object using the given Container to resolve dependencies.
     *
     * @template TObject of object
     *
     * @param class-string<TObject> $class     the class name
     * @param array<string,mixed>   $arguments optional constructor arguments passed to build the new class instance
     *
     * @throws ServiceIdMustBeNonEmptyStringException if $id is empty
     * @throws NotFoundExceptionInterface             if no entry was found for **this** identifier
     * @throws ContainerExceptionInterface            if there is an error while retrieving the entry
     * @throws CircularDependencyException            if a circular dependency is detected
     * @throws NotInstantiableException               if $class is not instantiable; (is an interface or an abstract class)
     * @throws ClassDoseNotExistException             if $class is not instantiable; (is an interface or an abstract class)
     *
     * @return TObject
     */
    public function build(string $class, array $arguments = []): object;

    /**
     * "Extend" a service object in the container.
     *
     * @template TObject of object
     *
     * @param class-string<TObject>          $class     the class name
     * @param callable(self,TObject):TObject $extension the callable
     *
     * @throws ServiceIdMustBeNonEmptyStringException if $class is empty
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
     * @throws ServiceIdMustBeNonEmptyStringException if $id is empty
     * @throws ServiceNotFoundException               if $id is not registered
     * @throws CircularDependencyException            if a circular dependency is detected
     * @throws NotInstantiableException               if $class is not instantiable; (is an interface or an abstract class)
     * @throws ClassDoseNotExistException             if $class is not instantiable; (is an interface or an abstract class)
     * @throws NotFoundExceptionInterface             if no entry was found for **this** identifier
     * @throws ContainerExceptionInterface            If error while retrieving the entry
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
     * Call any callable class or closure with optional arguments.
     *
     * @template TValue
     * @template TService
     *
     * @param callable(TValue):TService $callback
     * @param array<string,TValue>      $arguments optional arguments passed to $callback
     *
     * @throws ReflectionException
     * @throws Throwable
     *
     * @return TService
     */
    public function call(callable $callback, array $arguments = []): mixed;

    /** @param string $offset */
    public function offsetExists(mixed $offset): bool;

    /**
     * @param string $offset
     *
     * @throws Throwable
     */
    public function offsetGet(mixed $offset): mixed;

    /** @param string $offset */
    public function offsetSet(mixed $offset, mixed $value): void;

    /** @param string $offset */
    public function offsetUnset(mixed $offset): void;

    /**
     * Register a ServiceProvider class.
     *
     * Note: Service providers are automatically registered via `build` or `get` method.
     *
     * @param class-string<ServiceProviderInterface> $serviceProvider
     *
     * @throws ServiceProviderAlreadyRegisteredException                        if the ServiceProvider is already registered
     * @throws ServiceProviderMustBeSubclassOfServiceProviderInterfaceException if the ServiceProvider is not a subclass of ServiceProviderInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function register(string $serviceProvider): void;

    /**
     * Remove a registered service.
     *
     * @throws ServiceIdMustBeNonEmptyStringException if the service $id is empty
     * @throws ServiceNotFoundException               if the service $id is not registered
     */
    public function remove(string $id): void;

    /**
     * Reassigns a service on the given container.
     *
     * @template TService
     *
     * @param TService         $value
     * @param iterable<string> $tags
     *
     * @throws ServiceIdMustBeNonEmptyStringException if the service $id is empty
     * @throws ServiceAlreadyRegisteredException      if the service $id is already registered
     */
    public function replace(string $id, mixed $value, iterable $tags = []): void;

    /**
     * Resolves an alias to the service id.
     *
     * @throws ServiceIdMustBeNonEmptyStringException if the service $id is empty
     */
    public function resolve(string $id): string;

    /**
     * Assigns a service on the given container.
     *
     * @template TService
     *
     * @param TService         $value
     * @param iterable<string> $tags
     *
     * @throws ServiceIdMustBeNonEmptyStringException if the service $id is empty
     * @throws ServiceAlreadyRegisteredException      if the service $id is already registered
     */
    public function set(string $id, mixed $value, iterable $tags = []): void;

    /**
     * Assign a set of tags to a given service id.
     *
     * @param iterable<string> $tags
     *
     * @throws ServiceIdMustBeNonEmptyStringException  if the service $id is empty
     * @throws ServiceTagMustBeNonEmptyStringException if a service tag in $tags is empty
     */
    public function tag(string $id, iterable $tags): void;

    /**
     * Resolve services for a given tag.
     *
     * @template TService
     * @template TObject of object
     *
     * @return Generator<TObject|TService>
     */
    public function tagged(string $tag): Generator;
}
