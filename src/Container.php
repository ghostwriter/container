<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Contract\ExtensionInterface;
use Ghostwriter\Container\Contract\ServiceProviderInterface;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\InvalidArgumentException;
use Ghostwriter\Container\Exception\LogicException;
use Ghostwriter\Container\Exception\NotFoundException;
use Ghostwriter\Container\Exception\NotInstantiableException;
use Psr\Container\ContainerExceptionInterface as PsrContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

use function array_key_exists;
use function class_exists;
use function in_array;
use function is_callable;
use function trim;

/**
 * @see ContainerInterface
 *
 * @template GTServiceFactory of callable(ContainerInterface):object
 */
final class Container implements ContainerInterface
{
    private static ?ContainerInterface $instance = null;

    /** @var array<int,array<string,mixed>> $services */
    private array $services = [
        self::ALIASES      => [
            ContainerInterface::class    => self::class,
            PsrContainerInterface::class => self::class,
        ],
        self::DEPENDENCIES => [],
        self::EXTENSIONS   => [self::class => []],
        self::FACTORIES    => [],
        self::PROVIDERS    => [],
        self::SERVICES     => [self::class => null],
        self::TAGS         => [],
    ];

    private function __construct()
    {
    }

    /** @throws LogicException */
    public function __clone()
    {
        throw LogicException::dontClone(self::class);
    }

    public function __destruct()
    {
        self::$instance = null;
    }

    /** @throws LogicException */
    public function __serialize(): array
    {
        throw LogicException::dontSerialize(self::class);
    }

    /** @throws LogicException */
    public function __unserialize(array $data): void
    {
        throw LogicException::dontUnserialize(self::class);
    }

    /** @throws LogicException */
    public function register(ServiceProviderInterface $serviceProvider): void
    {
        if (array_key_exists($serviceProvider::class, self::$instance->services[self::PROVIDERS])) {
            throw LogicException::serviceProviderAlreadyRegistered($serviceProvider::class);
        }

        $serviceProvider(self::$instance);

        self::$instance->services[self::PROVIDERS][$serviceProvider::class] = true;
    }

    /** @throws LogicException */
    public function add(string $id, ExtensionInterface $extension): void
    {
        if (array_key_exists($extension::class, self::$instance->services[self::EXTENSIONS][self::class])) {
            throw LogicException::serviceExtensionAlreadyRegistered($extension::class);
        }

        $this->extend($id, $extension);

        self::$instance->services[self::EXTENSIONS][self::class][$extension::class] = true;
    }

    /**
     * Register an alias for another registered service.
     *
     *   $container->alias($container::class, ContainerInterface::class);
     *   $container->alias($container::class, PsrContainerInterface::class);
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function alias(string $alias, string $id): void
    {
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (trim($alias) === '') {
            throw InvalidArgumentException::emptyServiceAlias();
        }

        if ($alias === $id) {
            throw LogicException::serviceCannotAliasItself($id);
        }

        if (! self::$instance->has($id)) {
            throw NotFoundException::notRegistered($id);
        }

        self::$instance->services[self::ALIASES][$alias] = $id;
    }

    private function resolve(string $id): string
    {
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        while (array_key_exists($id, self::$instance->services[self::ALIASES])) {
            $id = self::$instance->services[self::ALIASES][$id];
        }

        return $id;
    }

    /**
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function bind(string $abstract, ?string $concrete = null): void
    {
        if (trim($abstract) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (
            array_key_exists($abstract, self::$instance->services[self::ALIASES]) ||
            array_key_exists($abstract, self::$instance->services[self::SERVICES]) ||
            array_key_exists($abstract, self::$instance->services[self::FACTORIES])
        ) {
            throw LogicException::serviceAlreadyRegistered($abstract);
        }

        self::$instance->services[self::FACTORIES][$abstract] =
            static fn (ContainerInterface $container): object =>
            $container->build($concrete ?? $abstract);
    }

    /**
     * Create an object using the given Container, to resolve dependencies.
     *
     * @param class-string|string $class A class name.
     * @param array<string,mixed> $arguments
     * @throws InvalidArgumentException The name parameter must be of non-empty string.
     * @throws NotInstantiableException Error while resolving the object.
     * @throws PsrContainerExceptionInterface
     */
    public function build(string $class, array $arguments = []): object
    {
        if (array_key_exists($class, $dependencies  = self::$instance->services[self::DEPENDENCIES])) {
            throw CircularDependencyException::instantiationStack($class, $dependencies);
        }

        try {
            if (! ($reflectionClass = new ReflectionClass($class))->isInstantiable()) {
                if ($class === self::class) {
                    return self::$instance;
                }
                throw NotInstantiableException::abstractClassOrInterface($class);
            }
        } catch (ReflectionException) {
            throw NotInstantiableException::classDoseNotExist($class);
        }

        if (! ($reflectionMethod = $reflectionClass->getConstructor()) instanceof ReflectionMethod) {
            return self::$instance->services[self::SERVICES][$class] = new $class();
        }

        self::$instance->services[self::DEPENDENCIES][$class] = $class;

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            if (array_key_exists($parameterName = $reflectionParameter->getName(), $arguments)) {
                continue;
            }

            if ($reflectionParameter->isOptional()) {
                continue;
            }

            if (
                ! ($parameterType = $reflectionParameter->getType()) instanceof ReflectionNamedType ||
                $parameterType->isBuiltin()
            ) {
                throw NotInstantiableException::unresolvableParameter(
                    $parameterName,
                    $reflectionMethod->getDeclaringClass()->getName()
                );
            }

            $arguments[$parameterName] = self::$instance->get($parameterType->getName());
        }

        unset(self::$instance->services[self::DEPENDENCIES][$class]);

        return self::$instance->services[self::SERVICES][$class] = new $class(...$arguments);
    }

    /**
     * "Extend" a service object in the container.
     *
     * @param callable(ContainerInterface,object):object $extension
     * @throws InvalidArgumentException
     */
    public function extend(string $class, callable $extension): void
    {
        if (trim($class) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (array_key_exists($class, $extensions = self::$instance->services[self::EXTENSIONS])) {
            self::$instance->services[self::EXTENSIONS][$class] = static fn(
                    ContainerInterface $container,
                    object $service
                ): object => $extension($container, $extensions[$class]($container, $service));

            return;
        }

        self::$instance->services[self::EXTENSIONS][$class] =
            static fn (ContainerInterface $container, object $service): object => $extension($container, $service);
    }

    /** @return iterable<string> */
    public function tagged(string $tag): iterable
    {
        yield from self::$instance->services[self::TAGS][$tag] ?? [];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PsrContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function offsetGet(mixed $offset): mixed
    {
        return self::$instance->get($offset);
    }

    public function offsetExists(mixed $offset): bool
    {
        return self::$instance->has($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        self::$instance->set($offset, $value);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function offsetUnset(mixed $offset): void
    {
        self::$instance->remove($offset);
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function remove(string $id): void
    {
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (! self::$instance->has($id)) {
            throw NotFoundException::notRegistered($id);
        }

        foreach ([self::ALIASES, self::EXTENSIONS, self::FACTORIES, self::SERVICES, self::TAGS] as $key) {
            if (array_key_exists($id, self::$instance->services[$key])) {
                unset(self::$instance->services[$key][$id]);
            }
        }
    }

    /** Instantiate the Container */
    public static function getInstance(): ContainerInterface
    {
        return self::$instance ??= new self();
    }

    public function has(string $id): bool
    {
        $id = self::$instance->resolve($id);

        return array_key_exists($id, self::$instance->services[self::SERVICES]) ||
            array_key_exists($id, self::$instance->services[self::FACTORIES]);
    }

    /**
     * Instantiate and return the service with the given id.
     *
     * Note: This method will return the same instance on subsequent calls.
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws PsrContainerExceptionInterface
     */
    public function get(string $id): mixed
    {
        $id = self::$instance->resolve($id);

        if (array_key_exists($id, self::$instance->services[self::SERVICES])) {
            if ($id === self::class) {
                return self::$instance;
            }
            return self::$instance->services[self::SERVICES][$id];
        }

        if (array_key_exists($id, self::$instance->services[self::FACTORIES])) {
            $service = self::$instance->services[self::FACTORIES][$id](self::$instance);

            if (array_key_exists($id, $extensions = self::$instance->services[self::EXTENSIONS])) {
                return self::$instance->services[self::SERVICES][$id] = $extensions[$id](self::$instance, $service);
            }

            return self::$instance->services[self::SERVICES][$id] = $service;
        }

        if (class_exists($id, true)) {
            return self::$instance->services[self::SERVICES][$id] = self::$instance->build($id);
        }

        throw NotFoundException::notRegistered($id);
    }

    /**
     * @param iterable<string> $tags
     */
    public function tag(string $id, iterable $tags): void
    {
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        $serviceTags = self::$instance->services[self::TAGS];

        foreach ($tags as $tag) {
            if (trim($tag) === '') {
                throw InvalidArgumentException::emptyServiceTagForServiceId($id);
            }

            if (array_key_exists($tag, $serviceTags) && in_array($id, $serviceTags[$tag], true)) {
                throw LogicException::serviceTagAlreadyRegistered($tag, $id);
            }

            $serviceTags[$tag][] = $id;
        }
        self::$instance->services[self::TAGS] = $serviceTags;
    }

    /**
     * Assigns a service on the given container.
     *
     * @param iterable<string> $tags
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function set(string $id, mixed $value, iterable $tags = []): void
    {
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (
            array_key_exists($id, self::$instance->services[self::SERVICES]) ||
            array_key_exists($id, self::$instance->services[self::FACTORIES])
        ) {
            throw LogicException::serviceAlreadyRegistered($id);
        }

        if (array_key_exists($id, self::$instance->services[self::ALIASES])) {
            unset(self::$instance->services[self::ALIASES][$id]);
        }

        self::$instance->services[is_callable($value, false) ?
            self::FACTORIES :
            self::SERVICES][$id] = $value;

        if ($tags === []) {
            return;
        }

        self::$instance->tag($id, $tags);
    }
}
