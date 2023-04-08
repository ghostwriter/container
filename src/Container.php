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
    private const ALIASES = 0;

    private const DEFAULT = [
        self::ALIASES => [
            ContainerInterface::class => self::class,
        ],
        self::DEPENDENCIES => [],
        self::EXTENSIONS => [],
        self::FACTORIES => [],
        self::PROVIDERS => [],
        self::SERVICES => [
            self::class => 0,
        ],
        self::TAGS => [],
    ];

    private const DEPENDENCIES = 1;

    private const EXTENSIONS = 2;

    private const FACTORIES = 3;

    private const PROVIDERS = 4;

    private const SERVICES = 5;

    private const TAGS = 6;

    /**
     * @var array{
     *     0:array<class-string|string,class-string|string>,
     *     1:array<class-string|string,bool>,
     *     2:array<class-string,array<array-key,callable(ContainerInterface,object):object>>,
     *     3:array<class-string|string,callable(ContainerInterface):object>,
     *     4:array<class-string,ServiceProviderInterface>,
     *     5:array<class-string|string,callable|object|scalar>,
     *     6:array<class-string|string,array<class-string|string>>,
     * }
     */
    private array $cache = self::DEFAULT;

    private static self|null $instance = null;

    private function __construct()
    {
        // singleton
    }

    public function __destruct()
    {
        self::$instance->cache = self::DEFAULT;
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
        if (trim($abstract) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if (trim($concrete) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if ($abstract === $concrete) {
            throw $this->throwInvalidArgument('Service "%s" can not use an alias with the same name.', $concrete);
        }

        if (! $this->has($concrete)) {
            throw $this->throwNotFoundException('Service "%s" was not found.', $concrete);
        }

        self::$instance->cache[self::ALIASES][$abstract] = $concrete;
    }

    public function bind(string $abstract, string|null $concrete = null, array $tags = []): void
    {
        $concrete ??= $abstract;
        if (trim($abstract) === '' || trim($concrete) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if (array_key_exists($abstract, self::$instance->cache[self::ALIASES]) ||
            array_key_exists($abstract, self::$instance->cache[self::SERVICES]) ||
            array_key_exists($abstract, self::$instance->cache[self::FACTORIES])
        ) {
            throw $this->throwInvalidArgument('Service AlreadyRegisteredException %s', $abstract);
        }

        self::$instance->cache[self::FACTORIES][$abstract] =
            static fn (ContainerInterface $container): object => $container->build($concrete);

        if ($tags === []) {
            return;
        }

        $this->tag($abstract, $tags);
    }

    public function build(string $class, array $arguments = []): object
    {
        if (trim($class) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if ($class === self::class) {
            return $this;
        }

        if (array_key_exists($class, self::$instance->cache[self::PROVIDERS])) {
            throw $this->throwInvalidArgument('ServiceProvider "%s" is already registered.', $class);
        }

        $dependencies = self::$instance->cache[self::DEPENDENCIES];

        if (array_key_exists($class, $dependencies)) {
            throw $this->throwNotFoundException(
                'Circular dependency: %s -> %s',
                implode(' -> ', array_keys($dependencies)),
                $class,
            );
        }

        $reflectionClass = Reflector::getReflectionClass($class);

        if (! $reflectionClass->isInstantiable()) {
            throw $this->throwInvalidArgument('Class "%s" is not instantiable.', $class);
        }

        $reflectionMethod = $reflectionClass->getConstructor();

        if (! $reflectionMethod instanceof ReflectionMethod) {
            $service = new $class();

            if ($service instanceof ServiceProviderInterface) {
                self::$instance->cache[self::PROVIDERS][$class] = true;
            }

            return self::$instance->cache[self::SERVICES][$class] = $service;
        }

        self::$instance->cache[self::DEPENDENCIES][$class] = true;

        $parameters = $this->buildParameters($reflectionMethod->getParameters(), $arguments);

        unset(self::$instance->cache[self::DEPENDENCIES][$class]);

        $service = new $class(...$parameters);

        if ($service instanceof ServiceProviderInterface) {
            self::$instance->cache[self::PROVIDERS][$class] = true;
        }

        return self::$instance->cache[self::SERVICES][$class] = $service;
    }

    public function call(callable|string $invokable, array $arguments = []): mixed
    {
        /** @var callable $callable */
        $callable = ! is_callable($invokable) && is_string($invokable) ?
            $this->get($invokable) :
            $invokable;

        $closure = $callable(...);

        return $closure(
            ...$this->buildParameters(iterator_to_array($this->getParametersForCallable($closure)), $arguments)
        );
    }

    public function extend(string $class, callable $extension): void
    {
        if (trim($class) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        $factories = self::$instance->cache[self::FACTORIES];
        $extensions = self::$instance->cache[self::EXTENSIONS];

        if (! array_key_exists($class, $extensions) &&
            ! array_key_exists($class, $factories) &&
            ! class_exists($class)
        ) {
            throw $this->throwNotFoundException('Service "%s" was not found.', $class);
        }

        self::$instance->cache[self::EXTENSIONS][$class] = array_key_exists($class, $extensions) ?
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

        if (array_key_exists($id, self::$instance->cache)) {
            return self::$instance->cache[$id];
        }

        if (array_key_exists($id, self::$instance->cache[self::SERVICES])) {
            return match (true) {
                $id === self::class => $this,
                default => self::$instance->cache[self::SERVICES][$id]
            };
        }

        $factories = self::$instance->cache[self::FACTORIES];

        if (! array_key_exists($id, $factories) && ! class_exists($id)) {
            throw $this->throwNotFoundException('Service "%s" was not found.', $id);
        }

        $serviceFactory = $factories[$id] ?? static fn (Container $container): object => $container->build($id);

        $extensions = self::$instance->cache[self::EXTENSIONS];

        return self::$instance->cache[self::SERVICES][$id] = array_key_exists($id, $extensions) ?
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

        return array_key_exists($id, self::$instance->cache[self::SERVICES]) ||
            array_key_exists($id, self::$instance->cache[self::FACTORIES]) ||
            array_key_exists($id, self::$instance->cache[self::ALIASES]);
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
        if (trim($id) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if (! $this->has($id)) {
            throw $this->throwNotFoundException('Service "%s" was not found.', $id);
        }

        unset(
            self::$instance->cache[self::ALIASES][$id],
            self::$instance->cache[self::EXTENSIONS][$id],
            self::$instance->cache[self::FACTORIES][$id],
            self::$instance->cache[self::SERVICES][$id],
            self::$instance->cache[self::TAGS][$id]
        );
    }

    public function replace(string $id, mixed $value, array $tags = []): void
    {
        $this->remove($id);
        $this->set($id, $value, $tags);
    }

    public function resolve(string $id): string
    {
        if (trim($id) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        $aliases = self::$instance->cache[self::ALIASES];
        while (array_key_exists($id, $aliases)) {
            $id = $aliases[$id];
        }

        return $id;
    }

    public function set(string $id, mixed $value, array $tags = []): void
    {
        if (trim($id) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        if (array_key_exists($id, self::$instance->cache[self::SERVICES])) {
            throw $this->throwServiceAlreadyRegisteredException($id);
        }

        if (array_key_exists($id, self::$instance->cache[self::FACTORIES])) {
            throw $this->throwServiceAlreadyRegisteredException($id);
        }

        if (array_key_exists($id, self::$instance->cache[self::ALIASES])) {
            throw $this->throwServiceAlreadyRegisteredException($id);
        }

        if (is_callable($value)) {
            self::$instance->cache[self::FACTORIES][$id] = $value;
        } else {
            self::$instance->cache[self::SERVICES][$id] = $value;
        }

        if ($tags === []) {
            return;
        }

        $this->tag($id, $tags);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function tag(string $id, array $tags): void
    {
        if (trim($id) === '') {
            throw $this->throwServiceIdMustBeNonEmptyString();
        }

        $serviceTags = self::$instance->cache[self::TAGS];

        foreach ($tags as $tag) {
            if (trim($tag) === '') {
                throw $this->throwServiceIdMustBeNonEmptyString();
            }

            $serviceTags[$tag][$id] ??= $id;
        }

        self::$instance->cache[self::TAGS] = $serviceTags;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return Generator<int, object, mixed, void>
     */
    public function tagged(string $tag): Generator
    {
        /** @var class-string|string $service */
        foreach (self::$instance->cache[self::TAGS][$tag] ?? [] as $service) {
            yield $this->get($service);
        }
    }

    private function buildParameters(array $reflectionParameters, array $arguments): array
    {
        return array_map(
            /**
             * @throws ContainerExceptionInterface
             * @throws NotFoundExceptionInterface
             */
            function (ReflectionParameter $reflectionParameter) use (&$arguments) {
                $parameterName = $reflectionParameter->getName();
                if ($arguments !== []) {
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
            },
            $reflectionParameters
        );
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
