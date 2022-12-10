<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Generator;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Contract\ExtensionInterface;
use Ghostwriter\Container\Contract\ServiceProviderInterface;
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
use Ghostwriter\Container\Exception\UnresolvableParameterException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use function array_key_exists;
use function array_reduce;
use function class_exists;
use function is_callable;
use function iterator_to_array;
use function trim;

/**
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
        throw new DontCloneException(self::class);
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
        throw new DontSerializeException(self::class);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __unserialize(array $data): void
    {
        throw new DontUnserializeException(self::class);
    }

    public function __unset(string $name): void
    {
        $this->remove($name);
    }

    public function add(string $id, ExtensionInterface $extension): void
    {
        if (array_key_exists($extension::class, $this->services[self::EXTENSIONS][self::class])) {
            throw new ServiceExtensionAlreadyRegisteredException($extension::class);
        }

        $this->extend($id, $extension);

        $this->services[self::EXTENSIONS][self::class][$extension::class] = true;
    }

    public function alias(string $id, string $alias): void
    {
        if ('' === trim($id)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        if ('' === trim($alias)) {
            throw new ServiceAliasMustBeNonEmptyStringException($id);
        }

        if ($alias === $id) {
            throw new ServiceCannotAliasItselfException($id);
        }

        if (! $this->has($id)) {
            throw new ServiceNotFoundException($id);
        }

        $this->services[self::ALIASES][$alias] = $id;
    }

    public function bind(string $abstract, ?string $concrete = null, iterable $tags = []): void
    {
        if ('' === trim($abstract)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        $concrete ??= $abstract;

        if ('' === trim($concrete)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        if (array_key_exists($abstract, $this->services[self::ALIASES])) {
            throw new ServiceAlreadyRegisteredException($abstract);
        }

        if (array_key_exists($abstract, $this->services[self::SERVICES])) {
            throw new ServiceAlreadyRegisteredException($abstract);
        }

        if (array_key_exists($abstract, $this->services[self::FACTORIES])) {
            throw new ServiceAlreadyRegisteredException($abstract);
        }

        $this->services[self::FACTORIES][$abstract] =
            static fn (ContainerInterface $container): object => $container->build($concrete);

        if ([] === $tags) {
            return;
        }

        $this->tag($abstract, $tags);
    }

    public function build(string $class, array $arguments = []): object
    {
        if ('' === trim($class)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        if (self::class === $class) {
            return $this;
        }

        if (array_key_exists($class, $this->services[self::PROVIDERS])) {
            throw new ServiceProviderAlreadyRegisteredException($class);
        }

        $dependencies = $this->services[self::DEPENDENCIES];

        if (array_key_exists($class, $dependencies)) {
            throw new CircularDependencyException($class, $dependencies);
        }

        try {
            $reflectionClass = $this->services[self::REFLECTIONS][$class] ??= new ReflectionClass($class);

            if (! $reflectionClass->isInstantiable()) {
                throw new NotInstantiableException($class);
            }
        } catch (ReflectionException) {
            throw new ClassDoseNotExistException($class);
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

            if (! $parameterType instanceof ReflectionNamedType || $parameterType->isBuiltin()) {
                throw new UnresolvableParameterException(
                    $parameterName,
                    $reflectionMethod->getName(),
                    $reflectionMethod->getDeclaringClass()
                        ->getName()
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
        if ('' === trim($class)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        $factories = $this->services[self::FACTORIES];
        $extensions = $this->services[self::EXTENSIONS];

        if (! array_key_exists($class, $extensions) &&
            ! array_key_exists($class, $factories) &&
            ! class_exists($class)
        ) {
            throw new ServiceNotFoundException($class);
        }

        $this->services[self::EXTENSIONS][$class] = array_key_exists($class, $extensions) ?
            static fn (
                ContainerInterface $container,
                object $service
            ): object => $extension($container, $extensions[$class]($container, $service)) :
            static fn (
                ContainerInterface $container,
                object $service
            ): object => $extension($container, $service);
    }

    public function get(string $id): mixed
    {
        $id = $this->resolve($id);

        if (self::class === $id) {
            return $this;
        }

        if (array_key_exists($id, $this->services[self::SERVICES])) {
            return $this->services[self::SERVICES][$id];
        }

        $factories = $this->services[self::FACTORIES];

        if (! array_key_exists($id, $factories) && ! class_exists($id)) {
            throw new ServiceNotFoundException($id);
        }

        $serviceFactory = $factories[$id] ?? static fn (Container $container): object => $container->build($id);

        $extensions = $this->services[self::EXTENSIONS];

        return $this->services[self::SERVICES][$id] = array_key_exists($id, $extensions) ?
            $extensions[$id]($this, $serviceFactory($this)) :
            $serviceFactory($this);
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

    public function call(callable $callback, array $arguments = []): mixed
    {
        $closure = Closure::fromCallable($callback);

        return $closure(
            ...array_reduce(
                iterator_to_array($this->getParametersForCallable($closure)),
                function (array $parameters, ReflectionParameter $reflectionParameter) use ($arguments): array {
                    $parameterName = $reflectionParameter->getName();

                    if (array_key_exists($parameterName, $arguments)) {
                        $parameters[$parameterName] = $arguments[$parameterName];
                        unset($arguments[$parameterName]);

                        return $parameters;
                    }

                    $reflectionType = $reflectionParameter->getType();

                    if ($reflectionType instanceof ReflectionNamedType && ! $reflectionType->isBuiltin()) {
                        $maybeVariadicParameter = $this->get($reflectionType->getName());

                        if ($reflectionParameter->isVariadic()) {
                            $maybeVariadicParameter = is_array($maybeVariadicParameter) ?
                                $maybeVariadicParameter :
                                [$maybeVariadicParameter];
                        }

                        $parameters[$parameterName] = $maybeVariadicParameter;

                        return $parameters;
                    }

                    if ([] !== $arguments) {
                        return $parameters;
                    }

                    $isDefaultValueAvailable = $reflectionParameter->isDefaultValueAvailable();

                    if ($isDefaultValueAvailable) {
                        $parameters[$parameterName] = $reflectionParameter->getDefaultValue();

                        return $parameters;
                    }

                    return match (true) {
                        $reflectionParameter->isOptional() => $parameters,
                        default => throw new UnresolvableParameterException(
                            $parameterName,
                            $reflectionParameter->getDeclaringClass()?->getName() ?? '',
                            $reflectionParameter->getDeclaringFunction()
                                ->getName()
                        )
                    };
                },
                []
            )
        );
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
            throw new ServiceProviderMustBeSubclassOfServiceProviderInterfaceException(
                ServiceProviderInterface::class
            );
        }

        $this->build($serviceProvider)($this);
    }

    public function remove(string $id): void
    {
        if ('' === trim($id)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        if (! $this->has($id)) {
            throw new ServiceNotFoundException($id);
        }

        foreach ([self::ALIASES, self::EXTENSIONS, self::FACTORIES, self::SERVICES, self::TAGS] as $key) {
            if (! array_key_exists($id, $this->services[$key])) {
                continue;
            }

            unset($this->services[$key][$id]);
        }
    }

    public function replace(string $id, mixed $value, iterable $tags = []): void
    {
        $this->remove($id);
        $this->set($id, $value, $tags);
    }

    public function resolve(string $id): string
    {
        if ('' === trim($id)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        while (array_key_exists($id, $this->services[self::ALIASES])) {
            $id = $this->services[self::ALIASES][$id];
        }

        return $id;
    }

    public function set(string $id, mixed $value, iterable $tags = []): void
    {
        if ('' === trim($id)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        if (array_key_exists($id, $this->services[self::SERVICES])) {
            throw new ServiceAlreadyRegisteredException($id);
        }

        if (array_key_exists($id, $this->services[self::FACTORIES])) {
            throw new ServiceAlreadyRegisteredException($id);
        }

        if (array_key_exists($id, $this->services[self::ALIASES])) {
            throw new ServiceAlreadyRegisteredException($id);
        }

        $this->services[is_callable($value) ? self::FACTORIES : self::SERVICES][$id] = $value;

        if ([] === $tags) {
            return;
        }

        $this->tag($id, $tags);
    }

    public function tag(string $id, iterable $tags): void
    {
        if ('' === trim($id)) {
            throw new ServiceIdMustBeNonEmptyStringException();
        }

        $serviceTags = $this->services[self::TAGS];

        foreach ($tags as $tag) {
            if ('' === trim($tag)) {
                throw new ServiceTagMustBeNonEmptyStringException();
            }

            $serviceTags[$tag][$id] ??= $id;
        }

        $this->services[self::TAGS] = $serviceTags;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function tagged(string $tag): Generator
    {
        /** @var class-string|string $service */
        foreach ($this->services[self::TAGS][$tag] ?? [] as $service) {
            yield $this->get($service);
        }
    }

    /**
     * @throws ReflectionException
     *
     * @return Generator<ReflectionParameter>
     */
    private function getParametersForCallable(Closure $closure): Generator
    {
        yield from (new ReflectionFunction($closure))->getParameters();
    }
}
