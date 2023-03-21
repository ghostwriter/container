<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Generator;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Contract\ServiceProviderInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

use function array_key_exists;
use function class_exists;
use function is_callable;
use function is_string;
use function iterator_to_array;
use function trim;

/**
 * @see \Ghostwriter\Container\Tests\Unit\ContainerTest
 */
final class Container implements ContainerInterface
{
    private const DEFAULT = [
        'aliases' => [
            ContainerInterface::class => self::class,
        ],
        'dependencies' => [],
        'extensions' => [],
        'factories' => [],
        'providers' => [],
        'services' => [
            self::class => 0,
        ],
        'tags' => [],
    ];

    private static ?self $instance = null;

    /**
     * @var array{
     *     aliases:array<class-string|string,class-string|string>,
     *     dependencies:array<class-string|string,bool>,
     *     extensions:array<class-string,array<array-key,callable(ContainerInterface,object):object>>,
     *     factories:array<class-string|string,callable(ContainerInterface):object>,
     *     providers:array<class-string,ServiceProviderInterface>,
     *     services:array<class-string|string,callable|object|scalar>,
     *     tags:array<class-string|string,array<class-string|string>>,
     * }
     */
    private array $vault = self::DEFAULT;

    private function __construct()
    {
        // singleton
    }

    public function __destruct()
    {
        $this->vault = self::DEFAULT;
    }

    public function __clone()
    {
        throw $this->throwContainerException(sprintf('Dont clone "%s".', self::class));
    }

    public function __serialize(): array
    {
        throw $this->throwContainerException(sprintf('Dont serialize "%s".', self::class));
    }

    public function __unserialize(array $data): void
    {
        throw $this->throwContainerException(sprintf('Dont unserialize "%s".', self::class));
    }

    public function alias(string $abstract, string $concrete): void
    {
        if ('' === trim($abstract)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if ('' === trim($concrete)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if ($abstract === $concrete) {
            throw $this->throwInvalidArgument('Service "%s" can not use an alias with the same name.', $concrete);
        }

        if (! $this->has($concrete)) {
            throw $this->throwNotFoundException('Service "%s" was not found.', $concrete);
        }

        $this->vault['aliases'][$abstract] = $concrete;
    }

    public function bind(string $abstract, ?string $concrete = null, array $tags = []): void
    {
        $concrete ??= $abstract;
        if ('' === trim($abstract)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if ('' === trim($concrete)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if (array_key_exists($abstract, $this->vault['aliases'])) {
            throw $this->throwInvalidArgument('Service AlreadyRegisteredException %s', $abstract);
        }

        if (array_key_exists($abstract, $this->vault['services'])) {
            throw $this->throwInvalidArgument('Service AlreadyRegisteredException %s', $abstract);
        }

        if (array_key_exists($abstract, $this->vault['factories'])) {
            throw $this->throwInvalidArgument('Service AlreadyRegisteredException %s', $abstract);
        }

        $this->vault['factories'][$abstract] =
            static fn (ContainerInterface $container): object => $container->build($concrete);

        if ([] === $tags) {
            return;
        }

        $this->tag($abstract, $tags);
    }

    public function build(string $class, array $arguments = []): object
    {
        if ('' === trim($class)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if (self::class === $class) {
            return $this;
        }

        if (array_key_exists($class, $this->vault['providers'])) {
            throw $this->throwInvalidArgument('ServiceProvider "%s" is already registered.', $class);
        }

        $dependencies = $this->vault['dependencies'];

        if (array_key_exists($class, $dependencies)) {
            throw $this->throwNotFoundException(
                'Circular dependency: %s -> %s',
                implode(' -> ', $dependencies),
                $class,
            );
        }

        try {
            $reflectionClass = new ReflectionClass($class);

            if (! $reflectionClass->isInstantiable()) {
                throw $this->throwContainerException('Class "%s" is not instantiable.', $class);
            }
        } catch (ReflectionException) {
            throw $this->throwNotFoundException('Class "%s" does not exist.', $class);
        }

        $reflectionMethod = $reflectionClass->getConstructor();

        if (! $reflectionMethod instanceof ReflectionMethod) {
            $service = new $class();

            if ($service instanceof ServiceProviderInterface) {
                $this->vault['providers'][$class] = true;
            }

            return $this->vault['services'][$class] = $service;
        }

        $this->vault['dependencies'][$class] = $class;

        $parameters = $this->buildParameters($reflectionMethod->getParameters(), $arguments);

        unset($this->vault['dependencies'][$class]);

        $service = new $class(...$parameters);

        if ($service instanceof ServiceProviderInterface) {
            $this->vault['providers'][$class] = true;
        }

        return $this->vault['services'][$class] = $service;
    }

    public function call(callable|string $invokable, array $arguments = []): mixed
    {
        /** @var callable $callable */
        $callable = ! is_callable($invokable) && is_string($invokable) ?
            $this->get($invokable) :
            $invokable;

        $closure = Closure::fromCallable($callable);

        return $closure(
            ...$this->buildParameters(iterator_to_array($this->getParametersForCallable($closure)), $arguments)
        );
    }

    public function extend(string $class, callable $extension): void
    {
        if ('' === trim($class)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        $factories = $this->vault['factories'];
        $extensions = $this->vault['extensions'];

        if (! array_key_exists($class, $extensions) &&
            ! array_key_exists($class, $factories) &&
            ! class_exists($class)
        ) {
            throw $this->throwNotFoundException('Service "%s" was not found.', $class);
        }

        $this->vault['extensions'][$class] = array_key_exists($class, $extensions) ?
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

        if (array_key_exists($id, $this->vault)) {
            return $this->vault[$id];
        }

        if (array_key_exists($id, $this->vault['services'])) {
            return $this->vault['services'][$id];
        }

        $factories = $this->vault['factories'];

        if (! array_key_exists($id, $factories) && ! class_exists($id)) {
            throw $this->throwNotFoundException('Service "%s" was not found.', $id);
        }

        $serviceFactory = $factories[$id] ?? static fn (Container $container): object => $container->build($id);

        $extensions = $this->vault['extensions'];

        return $this->vault['services'][$id] = array_key_exists($id, $extensions) ?
            $extensions[$id]($this, $serviceFactory($this)) :
            $serviceFactory($this);
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function has(string $id): bool
    {
        $id = $this->resolve($id);

        return array_key_exists($id, $this->vault['services']) ||
            array_key_exists($id, $this->vault['factories']) ||
            array_key_exists($id, $this->vault['aliases']);
    }

    public function register(string $serviceProvider): void
    {
        if (! is_subclass_of($serviceProvider, ServiceProviderInterface::class)) {
            throw $this->throwInvalidArgument(
                'ServiceProvider "%s" MUST implement "%s".',
                $serviceProvider,
                ServiceProviderInterface::class
            );
        }

        $this->build($serviceProvider)($this);
    }

    public function remove(string $id): void
    {
        if ('' === trim($id)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if (! $this->has($id)) {
            throw $this->throwNotFoundException('Service "%s" was not found.', $id);
        }

        unset(
            $this->vault['aliases'][$id],
            $this->vault['extensions'][$id],
            $this->vault['factories'][$id],
            $this->vault['services'][$id],
            $this->vault['tags'][$id]
        );
    }

    public function replace(string $id, mixed $value, array $tags = []): void
    {
        $this->remove($id);
        $this->set($id, $value, $tags);
    }

    public function resolve(string $id): string
    {
        if ('' === trim($id)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        $aliases = $this->vault['aliases'];
        while (array_key_exists($id, $aliases)) {
            $id = $aliases[$id];
        }

        return $id;
    }

    public function set(string $id, mixed $value, array $tags = []): void
    {
        if ('' === trim($id)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if (array_key_exists($id, $this->vault['services'])) {
            throw $this->throwServiceAlreadyRegisteredException($id);
        }

        if (array_key_exists($id, $this->vault['factories'])) {
            throw $this->throwServiceAlreadyRegisteredException($id);
        }

        if (array_key_exists($id, $this->vault['aliases'])) {
            throw $this->throwServiceAlreadyRegisteredException($id);
        }

        if (is_callable($value)) {
            $this->vault['factories'][$id] = $value;
        } else {
            $this->vault['services'][$id] = $value;
        }

        if ([] === $tags) {
            return;
        }

        $this->tag($id, $tags);
    }

    public function tag(string $id, array $tags): void
    {
        if ('' === trim($id)) {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        $serviceTags = $this->vault['tags'];

        foreach ($tags as $tag) {
            if ('' === trim($tag)) {
                throw $this->throwServiceIdMustBeNonEmptyString();
            }

            $serviceTags[$tag][$id] ??= $id;
        }

        $this->vault['tags'] = $serviceTags;
    }

    /**
     * @return Generator<int, object, mixed, void>
     */
    public function tagged(string $tag): Generator
    {
        /** @var class-string|string $service */
        foreach ($this->vault['tags'][$tag] ?? [] as $service) {
            yield $this->get($service);
        }
    }

    private function buildParameters(array $reflectionParameters, array $arguments): array
    {
        return array_map(function (ReflectionParameter $reflectionParameter) use (&$arguments) {
            $parameterName = $reflectionParameter->getName();
            if ([] !== $arguments) {
                $parameterKey =  array_key_exists($parameterName, $arguments) ?
                    $parameterName :
                    array_key_first($arguments);

                $parameter = $arguments[$parameterKey];

                unset($arguments[$parameterKey]);

                return $parameter;
            }

            if ($reflectionParameter->isDefaultValueAvailable()) {
                return $reflectionParameter->getDefaultValue();
            }

            $reflectionType = $reflectionParameter->getType();
            if ($reflectionType instanceof ReflectionNamedType && ! $reflectionType->isBuiltin()) {
                return $this->get($reflectionType->getName());
            }

            $name  = $reflectionParameter->getDeclaringFunction()
                ->getName();
            $isFunction = is_callable($name);

            throw $this->throwNotFoundException(
                'Unresolvable %s parameter "$%s" in "%s%s()"; does not have a default value.',
                $isFunction ? 'function' : 'class',
                $parameterName,
                $isFunction ? $name : $reflectionParameter->getDeclaringClass()?->getName(),
                $isFunction ? '' : '::' . $name
            );
        }, $reflectionParameters);
    }

    /**
     * @throws ReflectionException
     *
     * @return Generator<int<0, max>, ReflectionParameter, mixed, void>
     */
    private function getParametersForCallable(Closure $closure): Generator
    {
        yield from (new ReflectionFunction($closure))->getParameters();
    }

    private function throwContainerException(string $message, string ...$values): ContainerExceptionInterface
    {
        return new class(sprintf(
            $message,
            ...$values
        )) extends RuntimeException implements ContainerExceptionInterface {
        };
    }

    private function throwInvalidArgument(string $message, string ...$values): ContainerExceptionInterface
    {
        return new class(sprintf(
            $message,
            ...$values
        )) extends InvalidArgumentException implements ContainerExceptionInterface {
        };
    }

    private function throwNotFoundException(string $message, string ...$values): NotFoundExceptionInterface
    {
        return new class(sprintf($message, ...$values)) extends RuntimeException implements NotFoundExceptionInterface {
        };
    }

    private function throwServiceAlreadyRegisteredException(string $id): ContainerExceptionInterface
    {
        return $this->throwInvalidArgument('Service "%s" is already registered, user replace() instead.', $id);
    }

    private function throwServiceIdMustBeNonEmptyString(): ContainerExceptionInterface
    {
        return $this->throwInvalidArgument('Service Id MUST be a non-empty-string.');
    }
}
