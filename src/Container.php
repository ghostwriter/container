<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Generator;
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
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use function array_key_exists;
use function class_exists;
use function is_array;
use function is_callable;
use function trim;

/**
 * @implements ContainerInterface
 *
 * @see \Ghostwriter\Container\Tests\Unit\ContainerTest
 */
final class Container implements ContainerInterface
{
    private static ?ContainerInterface $instance = null;

    private array $services = self::DEFAULT_SERVICES;

    private function __construct()
    {
        // singleton
    }

    public function __destruct()
    {
        $this->services = self::DEFAULT_SERVICES;
    }

    public function __clone()
    {
        throw BadMethodCallException::dontClone(self::class);
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    public function __serialize(): array
    {
        throw BadMethodCallException::dontSerialize(self::class);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __unserialize(array $data): void
    {
        throw BadMethodCallException::dontUnserialize(self::class);
    }

    public function __unset(string $name): void
    {
        $this->remove($name);
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
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (trim($alias) === '') {
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

    public function bind(string $abstract, ?string $concrete = null, iterable $tags = []): void
    {
        if (trim($abstract) === '') {
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
            static fn (ContainerInterface $container): object => $container->build($concrete ?? $abstract);

        if ($tags === []) {
            return;
        }

        $this->tag($abstract, $tags);
    }

    public function build(string $class, array $arguments = []): object
    {
        if ($class === self::class) {
            return $this;
        }

        if (array_key_exists($class, $this->services[self::PROVIDERS])) {
            return $this->services[self::SERVICES][$class];
        }

        $dependencies = $this->services[self::DEPENDENCIES];
        if (array_key_exists($class, $dependencies)) {
            throw new CircularDependencyException(
                sprintf('Circular dependency: %s -> %s', implode(' -> ', $dependencies), $class)
            );
        }

        try {
            $reflectionClass = $this->services[self::REFLECTIONS][$class] ??= new ReflectionClass($class);
            if (! $reflectionClass->isInstantiable()) {
                throw NotInstantiableException::abstractClassOrInterface($class);
            }
        } catch (ReflectionException) {
            throw NotInstantiableException::classDoseNotExist($class);
        }

        $reflectionMethod = $reflectionClass->getConstructor();
        if (! $reflectionMethod instanceof ReflectionMethod) {
            $service = new $class();

            if ($service instanceof ServiceProviderInterface) {
                $this->services[self::PROVIDERS][$class] = true;
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
            if (
                ! $parameterType instanceof ReflectionNamedType ||
                $parameterType->isBuiltin()
            ) {
                throw NotInstantiableException::unresolvableParameter(
                    $parameterName,
                    $reflectionMethod->getDeclaringClass()
                        ->getName(),
                    $reflectionMethod->getName()
                );
            }

            $arguments[$parameterName] = $this->get($parameterType->getName());
        }

        unset($this->services[self::DEPENDENCIES][$class]);

        $service = new $class(...$arguments);

        if ($service instanceof ServiceProviderInterface) {
            $this->services[self::PROVIDERS][$class] = true;
        }

        return $this->services[self::SERVICES][$class] = $service;
    }

    public function extend(string $class, callable $extension): void
    {
        if (trim($class) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (! array_key_exists($class, $this->services[self::FACTORIES])) {
            throw NotFoundException::notRegistered($class);
        }

        $extensions = $this->services[self::EXTENSIONS];

        $this->services[self::EXTENSIONS][$class] = array_key_exists($class, $extensions) ?
            static fn (ContainerInterface $container, object $service): object => $extension($container, $extensions[$class]($container, $service)) :
            static fn (ContainerInterface $container, object $service): object => $extension($container, $service);
    }

    public function get(string $id): mixed
    {
        $id = $this->resolve($id);

        if ($id === self::class) {
            return $this;
        }

        if (array_key_exists($id, $this->services[self::SERVICES])) {
            return $this->services[self::SERVICES][$id];
        }

        $factories = $this->services[self::FACTORIES];
        if (array_key_exists($id, $factories) || class_exists($id)) {
            $service = $factories[$id] ?? static fn (Container $container): object => $container->build($id);

            $extensions = $this->services[self::EXTENSIONS];

            return $this->services[self::SERVICES][$id] = array_key_exists($id, $extensions) ?
                $extensions[$id]($this, $service($this)) :
                $service($this);
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
            array_key_exists($id, $this->services[self::FACTORIES]) ||
            array_key_exists($id, $this->services[self::ALIASES]);
    }

    public function invoke(callable $callback, array $arguments = []): mixed
    {
        $parameters = is_array($callback) ?
                (new ReflectionClass($callback[0]))->getMethod($callback[1])->getParameters() :
                (new ReflectionFunction(Closure::fromCallable($callback)))->getParameters();

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            if (array_key_exists($parameterName, $arguments)) {
                continue;
            }

            if ($parameter->isOptional()) {
                continue;
            }

            $parameterType = $parameter->getType();
            if (
                ! $parameterType instanceof ReflectionNamedType ||
                $parameterType->isBuiltin()
            ) {
                $reflectionClass = $parameter->getDeclaringClass();
                throw NotInstantiableException::unresolvableParameter(
                    $parameterName,
                    $reflectionClass instanceof ReflectionClass ?
                        $reflectionClass->getName() : '',
                    $parameter->getDeclaringFunction()
                        ->getName()
                );
            }

            $arguments[$parameterName] = $this->get($parameterType->getName());
        }

        return $callback(...$arguments);
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

    public function register(string $serviceProvider): void
    {
        if (! is_subclass_of($serviceProvider, ServiceProviderInterface::class)) {
            throw new InvalidArgumentException(
                sprintf('$service MUST be an instance of %s', ServiceProviderInterface::class)
            );
        }
        $this->build($serviceProvider)($this);
    }

    public function remove(string $id): void
    {
        if (trim($id) === '') {
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

    public function replace(string $id, mixed $value, iterable $tags = []): void
    {
        $this->remove($id);
        $this->set($id, $value, $tags);
    }

    public function resolve(string $id): string
    {
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        while (array_key_exists($id, $this->services[self::ALIASES])) {
            $id = $this->services[self::ALIASES][$id];
        }

        return $id;
    }

    public function set(string $id, mixed $value, iterable $tags = []): void
    {
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        if (
            array_key_exists($id, $this->services[self::SERVICES]) ||
            array_key_exists($id, $this->services[self::FACTORIES]) ||
            array_key_exists($id, $this->services[self::ALIASES])
        ) {
            throw LogicException::serviceAlreadyRegistered($id);
        }

        $this->services[is_callable($value, false) ?
            self::FACTORIES :
            self::SERVICES][$id] = $value;

        if ($tags === []) {
            return;
        }

        $this->tag($id, $tags);
    }

    public function tag(string $id, iterable $tags): void
    {
        if (trim($id) === '') {
            throw InvalidArgumentException::emptyServiceId();
        }

        $serviceTags = $this->services[self::TAGS];

        foreach ($tags as $tag) {
            if (trim($tag) === '') {
                throw InvalidArgumentException::emptyServiceTagForServiceId($id);
            }

            $serviceTags[$tag][$id] ??= $id;
        }

        $this->services[self::TAGS] = $serviceTags;
    }

    public function tagged(string $tag): Generator
    {
        yield from $this->services[self::TAGS][$tag] ?? [];
    }
}
