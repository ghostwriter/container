<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Generator;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\DontCloneContainerException;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Exception\ServiceExtensionAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException;
use Ghostwriter\Container\Exception\ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException;
use Ghostwriter\Container\Exception\ServiceMustBeAnObjectException;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use InvalidArgumentException;
use Throwable;
use function array_key_exists;
use function array_key_last;
use function array_keys;
use function array_reduce;
use function class_exists;
use function implode;
use function in_array;
use function interface_exists;
use function is_a;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function mb_strpos;
use function mb_substr;
use function sprintf;
use function str_contains;
use function trim;

/**
 * @see \Ghostwriter\ContainerTests\Unit\ContainerTest
 */
final class Container implements ContainerInterface
{
    public const DEFAULT = [
        self::ALIASES => [
            ContainerInterface::class => self::class,
        ],
        self::EXTENSIONS => [],
        self::BINDINGS => [],
        self::FACTORIES => [],
        self::DEPENDENCIES => [],
        self::SERVICES => [],
        self::INSTANCES => [],
        self::PROVIDERS => [],
        self::TAGS => [],
    ];

    /**
     * @template TService of object
     *
     * @var array<class-string<TService>, class-string<TService>>
     * @var array<class-string<TService>, class-string<TService>>
     * @var array<class-string<TService>,list<ExtensionInterface<TService>>>
     * @var array<class-string<TService>,callable():TService>
     * @var array<class-string<ServiceProviderInterface>, null>
     * @var array<class-string<TService>, TService>
     * @var array<callable-string|class-string<TService>>
     * @var array<class-string<TService>,list<string>>
     * @var array{
     *     0: array<class-string<TService>, class-string<TService>>,
     *     1: array<class-string<TService>,list<ExtensionInterface<TService>>>,
     *     2: array<class-string<TService>, class-string<TService>>,
     *     3: array<class-string<TService>,callable():TService>,
     *     4: array<class-string<TService>, null>,
     *     5: array<class-string<TService>, TService>,
     *     6: array<callable-string|class-string<TService>>,
     *     7: array<class-string<TService>,list<string>>,
     *     7: array<class-string<ServiceProviderInterface>,null>,
     *     8: array<string, array<string, string>>,
     * }
     */
    public static array $cache = self::DEFAULT;

    private static self $instance;

    private readonly Instantiator $instantiator;

    private readonly ParameterBuilder $parameterBuilder;

    private readonly Reflector $reflector;

    private function __construct()
    {
        $this->reflector = Reflector::new();
        $this->parameterBuilder = ParameterBuilder::new($this);
        $this->instantiator = Instantiator::new($this->reflector, $this->parameterBuilder);
    }

    /**
     * Remove all registered services from this container and reset the default services.
     */
    public function __destruct()
    {
        self::$cache = self::DEFAULT;
    }

    /**
     * @throws ExceptionInterface if "__clone()" method is called
     */
    public function __clone(): void
    {
        throw new DontCloneContainerException();
    }

    /**
     * @throws ExceptionInterface if "__serialize()" method is called
     */
    public function __serialize(): array
    {
        throw new DontSerializeContainerException();
    }

    /**
     * @template TMixed
     *
     * @param array<TMixed> $data
     *
     * @throws ExceptionInterface if "__unserialize()" method is called
     */
    public function __unserialize(array $data): void
    {
        throw new DontUnserializeContainerException();
    }

    public function alias(string $name, string $service): void
    {
        if (trim($name) === '') {
            throw new AliasNameMustBeNonEmptyStringException();
        }

        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($name === $service) {
            throw new AliasNameAndServiceNameCannotBeTheSameException($name);
        }

        self::$cache[self::ALIASES][$name] = $service;
    }

    /**
     * @template TConcreteClass of object
     * @template TAbstractClass of object
     * @template TImplementationClass of object
     *
     * @param class-string<TConcreteClass>       $concrete
     * @param class-string<TAbstractClass>       $abstract
     * @param class-string<TImplementationClass> $implementation
     */
    public function bind(
        string $concrete,
        string $abstract,
        string $implementation
    ): void {
        if (trim($concrete) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($concrete)) {
            throw new ServiceNotFoundException($concrete);
        }

        if (trim($abstract) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($abstract) &&
            ! interface_exists($abstract)
        ) {
            throw new ServiceNotFoundException($abstract);
        }

        if (trim($implementation) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! class_exists($implementation) &&
            ! interface_exists($implementation)
        ) {
            throw new ServiceNotFoundException($implementation);
        }

        self::$cache[self::BINDINGS][$concrete][$abstract] = $implementation;
    }

    /**
     * @template TArgument
     * @template TService of object
     *
     * @param class-string<TService> $service
     * @param array<TArgument>       $arguments
     *
     * @throws CircularDependencyException
     * @throws ClassNotInstantiableException
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws Throwable
     *
     * @return TService
     */
    public function build(string $service, array $arguments = []): object
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (is_a($service, ContainerInterface::class, true)) {
            return $this;
        }

        while (array_key_exists($service, self::$cache[self::ALIASES])) {
            $service = self::$cache[self::ALIASES][$service];
        }

        if (array_key_exists($service, self::$cache[self::DEPENDENCIES])) {
            throw new CircularDependencyException(sprintf(
                'Class: %s -> %s',
                implode(' -> ', array_keys(self::$cache[self::DEPENDENCIES])),
                $service
            ));
        }

        self::$cache[self::DEPENDENCIES][$service] = true;

        /** @var TService $instance */
        $instance = match (true) {
            array_key_exists($service, self::$cache[self::FACTORIES]) =>
            $this->call(self::$cache[self::FACTORIES][$service], $arguments),
            default => $this->instantiator->instantiate($service, $arguments)
        };

        if (array_key_exists($service, self::$cache[self::DEPENDENCIES])) {
            unset(self::$cache[self::DEPENDENCIES][$service]);
        }

        if (! is_object($instance)) {
            throw new ServiceMustBeAnObjectException($service);
        }

        self::$cache[self::INSTANCES][$service] = $instance;

        foreach (array_keys(self::$cache[self::EXTENSIONS]) as $serviceName) {
            if ($serviceName !== $service
                && ! is_a($instance, $serviceName, true)
            ) {
                continue;
            }

            foreach (self::$cache[self::EXTENSIONS][$serviceName] ?? [] as $extension) {
                $instance = $this->invoke($extension, [$this, $instance]);
            }
        }

        return self::$cache[self::INSTANCES][$service] = $instance;
    }

    /**
     * @template TArgument
     * @template TResult
     *
     * @param callable(array<TArgument>):TResult $callback
     * @param array<TArgument>                   $arguments
     *
     * @throws Throwable
     *
     * @return TResult
     */
    public function call(callable $callback, array $arguments = []): mixed
    {
        return $callback(
            ...$this->parameterBuilder->build(
                match (true) {
                    is_string($callback) => match (true) {
                        str_contains($callback, '::') => (function (string $callback) {
                            $position = mb_strpos($callback, '::');

                            $class = mb_substr($callback, 0, $position);

                            $method = mb_substr($callback, $position + 2);

                            return $this->reflector->reflectClass($class)->getMethod($method)->getParameters();
                        })($callback),
                        default => $this->reflector->reflectFunction($callback)->getParameters(),
                    },
                    $callback instanceof Closure => $this->reflector
                        ->reflectFunction($callback)
                        ->getParameters(),
                    is_array($callback) => match (true) {
                        is_object($callback[0]) => $this->reflector
                            ->reflectClass($callback[0]::class)
                            ->getMethod($callback[1])
                            ->getParameters(),
                        default => $this->reflector
                            ->reflectClass($callback[0])
                            ->getMethod($callback[1])
                            ->getParameters(),
                    },
                    default => $this->reflector
                        ->reflectClass($callback::class)
                        ->getMethod('__invoke')
                        ->getParameters()
                } ?? [],
                $arguments
            )
        );
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService>                     $service
     * @param class-string<ExtensionInterface<TService>> $extension
     */
    public function extend(string $service, string $extension): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! is_a($extension, ExtensionInterface::class, true)
            || $extension === ExtensionInterface::class
        ) {
            throw new ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException($extension);
        }

        if (array_key_exists($extension, self::$cache[self::EXTENSIONS][$service] ?? [])) {
            throw new ServiceExtensionAlreadyRegisteredException($extension);
        }

        self::$cache[self::EXTENSIONS][$service][$extension] = $extension;
    }

    /**
     * Provide a FactoryInterface for a service.
     *
     * @template TService of object
     *
     * @param class-string<TService>                   $service
     * @param class-string<FactoryInterface<TService>> $serviceFactory
     */
    public function factory(string $service, string $serviceFactory): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (! is_a($serviceFactory, FactoryInterface::class, true)
            || $serviceFactory === FactoryInterface::class
        ) {
            throw new ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException($serviceFactory);
        }

        self::$cache[self::FACTORIES][$service] = static fn (ContainerInterface $container): object => $container->invoke($serviceFactory);
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     *
     * @return TService
     */
    public function get(string $service): object
    {
        $class = $this->resolve($service);

        $instances = self::$cache[self::INSTANCES];

        return match (true) {
            array_key_exists($class, $instances) => $instances[$class],
            is_a($class, ContainerInterface::class, true) => $this,
            ! class_exists($class) => throw new ServiceNotFoundException($class),
            default => $this->build($class),
        };
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     */
    public function has(string $service): bool
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        return match (true) {
            default => array_reduce(
                self::$cache[self::BINDINGS],
                /**
                 * @param array<class-string<TService>> $binding
                 */
                static fn (bool $carry, array $binding): bool => $carry
                    || in_array($service, $binding, true),
                false
            ),
            array_key_exists($service, self::$cache[self::INSTANCES]),
            array_key_exists($service, self::$cache[self::FACTORIES]),
            array_key_exists($service, self::$cache[self::SERVICES]),
            array_key_exists($service, self::$cache[self::ALIASES]),
            is_a($service, ContainerInterface::class, true) => true,
        };
    }

    /**
     * @template TInvokable of object
     * @template TArgument
     * @template TResult
     *
     * @param callable-string|class-string<TInvokable> $invokable
     * @param array<TArgument>                         $arguments
     *
     * @throws Throwable
     */
    public function invoke(string $invokable, array $arguments = []): mixed
    {
        /** @var callable(array<TArgument>):TResult $callable */
        $callable = $this->get($invokable);

        return $this->call($callable, $arguments);
    }

    /**
     * @param class-string<ServiceProviderInterface> $serviceProvider
     *
     * @throws ServiceProviderAlreadyRegisteredException
     * @throws ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException
     * @throws Throwable
     */
    public function provide(string $serviceProvider): void
    {
        if (! is_a($serviceProvider, ServiceProviderInterface::class, true)
            || $serviceProvider === ServiceProviderInterface::class
        ) {
            throw new ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException($serviceProvider);
        }

        if (array_key_exists($serviceProvider, self::$cache[self::PROVIDERS])) {
            throw new ServiceProviderAlreadyRegisteredException($serviceProvider);
        }

        self::$cache[self::PROVIDERS][$serviceProvider] = null;

        $this->invoke($serviceProvider);
    }

    public function register(string $abstract, string $concrete = null, array $tags = []): void
    {
        $concrete ??= $abstract;

        if (trim($abstract) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (trim($concrete) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($abstract !== $concrete) {
            self::$cache[self::ALIASES][$abstract] = $concrete;
        }

        self::$cache[self::SERVICES][$concrete] ??= $concrete;

        if ($tags !== []) {
            $this->tag($abstract, $tags);
        }
    }

    public function remove(string $service): void
    {
        unset(
            self::$cache[self::ALIASES][$service],
            self::$cache[self::EXTENSIONS][$service],
            self::$cache[self::FACTORIES][$service],
            self::$cache[self::INSTANCES][$service],
            self::$cache[self::SERVICES][$service],
            self::$cache[self::TAGS][$service],
        );
    }

    /**
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws ServiceTagMustBeNonEmptyStringException
     * @throws ExceptionInterface
     */
    public function set(string $service, callable|object $value, array $tags = []): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($tags !== []) {
            $this->tag($service, $tags);
        }

        if (is_callable($value)) {
            self::$cache[self::FACTORIES][$service] = $value;
            return;
        }

        if (is_object($value)) {
            self::$cache[self::INSTANCES][$service] = $value;
            return;
        }

        throw new ServiceMustBeAnObjectException($service);
    }

    /**
     * @throws ExceptionInterface
     */
    public function tag(string $service, array $tags): void
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        foreach ($tags as $tag) {
            if (trim($tag) === '') {
                throw new ServiceTagMustBeNonEmptyStringException();
            }

            self::$cache[self::TAGS][$tag][$service] = $service;
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $tag
     *
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     * @throws ExceptionInterface
     *
     * @return Generator<TService>
     */
    public function tagged(string $tag): Generator
    {
        if (trim($tag) === '') {
            throw new ServiceTagMustBeNonEmptyStringException();
        }

        if (! array_key_exists($tag, self::$cache[self::TAGS])) {
            throw new ServiceTagNotFoundException($tag);
        }

        /** @var class-string<TService> $service */
        foreach (self::$cache[self::TAGS][$tag] as $service) {
            yield $this->get($service);
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     * @param list<string>           $tags
     */
    public function untag(string $service, array $tags): void
    {
        foreach ($tags as $tag) {
            if (! array_key_exists($tag, self::$cache[self::TAGS])) {
                throw new ServiceTagNotFoundException($tag);
            }

            if (! array_key_exists($service, self::$cache[self::TAGS][$tag])) {
                throw new ServiceNotFoundException($tag);
            }

            unset(self::$cache[self::TAGS][$tag][$service]);
        }
    }

    /**
     * @template TService of object
     *
     * @param class-string<TService> $service
     *
     * @throws ServiceNameMustBeNonEmptyStringException
     */
    private function resolve(string $service): string
    {
        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        while (array_key_exists($service, self::$cache[self::ALIASES])) {
            $service = self::$cache[self::ALIASES][$service];
        }

        $bindings = self::$cache[self::BINDINGS] ?? [];

        $dependencies = self::$cache[self::DEPENDENCIES] ?? [];

        if ($bindings === [] || $dependencies === []) {
            return $service;
        }

        return $bindings[array_key_last($dependencies)][$service] ?? $service;
    }
}
