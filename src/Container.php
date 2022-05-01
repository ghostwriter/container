<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\Container\Contract\ExtensionInterface;
use Ghostwriter\Container\Contract\ServiceProviderInterface;
use Ghostwriter\Container\Exception\BadMethodCallException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\InvalidArgumentException;
use Ghostwriter\Container\Exception\LogicException;
use Ghostwriter\Container\Exception\NotFoundException;
use Ghostwriter\Container\Exception\NotInstantiableException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

use function array_key_exists;
use function class_exists;
use function is_callable;
use function trim;

/**
 * @implements ContainerInterface
 */
final class Container implements ContainerInterface
{
    private static ?ContainerInterface $instance = null;

    /**
     * @var array{
     *     aliases: array<string,string>,
     *     dependencies: array<string,bool>,
     *     extensions: array<string,callable(ContainerInterface, object):object>,
     *     factories: array<string,callable(ContainerInterface):object>,
     *     providers: array<string,ServiceProviderInterface>,
     *     services: array<string,int|object|float|callable|string|null|bool>,
     *     tags: array<string,array<string>>,
     * } $services
     */
    private array $services = self::DEFAULT_SERVICES;

    private function __construct()
    {
        /** singleton */
    }

    public function __destruct()
    {
        $this->services = self::DEFAULT_SERVICES;
    }

    public function __clone()
    {
        throw BadMethodCallException::dontClone(self::class);
    }

    public function __serialize(): array
    {
        throw BadMethodCallException::dontSerialize(self::class);
    }

    public function __unserialize(array $data): void
    {
        throw BadMethodCallException::dontUnserialize(self::class);
    }

    public function add(string $id, ExtensionInterface $extension): void
    {
        if (array_key_exists($extension::class, $this->services[self::EXTENSIONS][self::class])) {
            throw LogicException::serviceExtensionAlreadyRegistered($extension::class);
        }

        $this->extend($id, $extension);

        $this->services[self::EXTENSIONS][self::class][$extension::class] = true;
    }

    public function alias(string $alias, string $id): void
    {
        if ('' === trim($id)) {
            throw InvalidArgumentException::emptyServiceId();
        }

        if ('' === trim($alias)) {
            throw InvalidArgumentException::emptyServiceAlias();
        }

        if ($alias === $id) {
            throw LogicException::serviceCannotAliasItself($id);
        }

        if (! $this->has($id)) {
            throw NotFoundException::notRegistered($id);
        }

        $this->services[self::ALIASES][$alias] = $id;
    }

    public function bind(string $abstract, ?string $concrete = null): void
    {
        if ('' === trim($abstract)) {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (
            array_key_exists($abstract, $this->services[self::ALIASES]) ||
            array_key_exists($abstract, $this->services[self::SERVICES]) ||
            array_key_exists($abstract, $this->services[self::FACTORIES])
        ) {
            throw LogicException::serviceAlreadyRegistered($abstract);
        }

        $this->services[self::FACTORIES][$abstract] =
            static fn (ContainerInterface $container): object =>
            $container->build($concrete ?? $abstract);
    }

    public function build(string $class, array $arguments = []): object
    {
        if (self::class === $class) {
            return $this;
        }

        $dependencies  = $this->services[self::DEPENDENCIES];
        if (array_key_exists($class, $dependencies)) {
            throw CircularDependencyException::detected($class, $dependencies);
        }

        try {
            $reflectionClass = new ReflectionClass($class);
            if (! $reflectionClass->isInstantiable()) {
                throw NotInstantiableException::abstractClassOrInterface($class);
            }
        } catch (ReflectionException) {
            throw NotInstantiableException::classDoseNotExist($class);
        }

        $reflectionMethod = $reflectionClass->getConstructor();
        if (! $reflectionMethod  instanceof ReflectionMethod) {
            $service =  new $class();

            if (
                $service instanceof ServiceProviderInterface &&
                ! array_key_exists($class, $this->services[self::PROVIDERS])
            ) {
                $this->register($service);
            }

            return $this->services[self::SERVICES][$class] = $service;
        }

        $this->services[self::DEPENDENCIES][$class] = $class;

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();
            if (array_key_exists($parameterName, $arguments)) {
                continue;
            }

            if ($reflectionParameter->isOptional()) {
                continue;
            }

            $parameterType = $reflectionParameter->getType();
            if (! ($parameterType) instanceof ReflectionNamedType ||
                $parameterType->isBuiltin()
            ) {
                throw NotInstantiableException::unresolvableParameter(
                    $parameterName,
                    $reflectionMethod->getDeclaringClass()->getName()
                );
            }

            $arguments[$parameterName] = $this->get($parameterType->getName());
        }

        unset($this->services[self::DEPENDENCIES][$class]);

        $service =  new $class(...$arguments);

        if (
            $service instanceof ServiceProviderInterface &&
            ! array_key_exists($class, $this->services[self::PROVIDERS])
        ) {
            $this->register($service);
        }

        return $this->services[self::SERVICES][$class] = $service;
    }

    public function extend(string $class, callable $extension): void
    {
        if ('' === trim($class)) {
            throw InvalidArgumentException::emptyServiceId();
        }

        $extensions = $this->services[self::EXTENSIONS];
        if (array_key_exists($class, $extensions)) {
            $this->services[self::EXTENSIONS][$class] =
                static fn (ContainerInterface $container, object $service): object => $extension($container, $extensions[$class]($container, $service));

            return;
        }

        $this->services[self::EXTENSIONS][$class] =
            static fn (ContainerInterface $container, object $service): object => $extension($container, $service);
    }

    public function get(string $id): mixed
    {
        $id = $this->resolve($id);

        if (array_key_exists($id, $this->services[self::SERVICES])) {
            if (self::class === $id) {
                return self::$instance;
            }

            return $this->services[self::SERVICES][$id];
        }

        if (array_key_exists($id, $this->services[self::FACTORIES])) {
            $service = $this->services[self::FACTORIES][$id](self::$instance);

            $extensions = $this->services[self::EXTENSIONS];
            if (array_key_exists($id, $extensions)) {
                return $this->services[self::SERVICES][$id] = $extensions[$id](self::$instance, $service);
            }

            return $this->services[self::SERVICES][$id] = $service;
        }

        if (class_exists($id, true)) {
            return $this->services[self::SERVICES][$id] = $this->build($id);
        }

        throw NotFoundException::notRegistered($id);
    }

    public static function getInstance(): ContainerInterface
    {
        return self::$instance ??= new self();
    }

    public function has(string $id): bool
    {
        $id = $this->resolve($id);

        return array_key_exists($id, $this->services[self::SERVICES]) ||
            array_key_exists($id, $this->services[self::FACTORIES]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    public function register(ServiceProviderInterface $serviceProvider): void
    {
        if (array_key_exists($serviceProvider::class, $this->services[self::PROVIDERS])) {
            throw LogicException::serviceProviderAlreadyRegistered($serviceProvider::class);
        }

        $serviceProvider(self::$instance);

        $this->services[self::PROVIDERS][$serviceProvider::class] = true;
    }

    public function remove(string $id): void
    {
        if ('' === trim($id)) {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (! $this->has($id)) {
            throw NotFoundException::notRegistered($id);
        }

        foreach ([self::ALIASES, self::EXTENSIONS, self::FACTORIES, self::SERVICES, self::TAGS] as $key) {
            if (array_key_exists($id, $this->services[$key])) {
                unset($this->services[$key][$id]);
            }
        }
    }

    public function resolve(string $id): string
    {
        if ('' === trim($id)) {
            throw InvalidArgumentException::emptyServiceId();
        }

        while (array_key_exists($id, $this->services[self::ALIASES])) {
            $id = $this->services[self::ALIASES][$id];
        }

        return $id;
    }

    public function set(string $id, mixed $value = null, iterable $tags = []): void
    {
        if ('' === trim($id)) {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (
            array_key_exists($id, $this->services[self::SERVICES]) ||
            array_key_exists($id, $this->services[self::FACTORIES])
        ) {
            throw LogicException::serviceAlreadyRegistered($id);
        }

        if (array_key_exists($id, $this->services[self::ALIASES])) {
            unset($this->services[self::ALIASES][$id]);
        }

        $this->services[is_callable($value, false) ?
            self::FACTORIES :
            self::SERVICES][$id] = $value;

        if ([] === $tags) {
            return;
        }

        $this->tag($id, $tags);
    }

    public function tag(string $id, iterable $tags): void
    {
        if ('' === trim($id)) {
            throw InvalidArgumentException::emptyServiceId();
        }

        $serviceTags = $this->services[self::TAGS];

        foreach ($tags as $tag) {
            if ('' === trim($tag)) {
                throw InvalidArgumentException::emptyServiceTagForServiceId($id);
            }

            $serviceTags[$tag][$id] ??= $id;
        }

        $this->services[self::TAGS] = $serviceTags;
    }

    public function tagged(string $tag): iterable
    {
        yield from $this->services[self::TAGS][$tag] ?? [];
    }
}
