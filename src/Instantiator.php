<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Throwable;
use ReflectionClass;
use function sprintf;

/** @see Ghostwriter\Container\Tests\Unit\InstantiatorTest */
final readonly class Instantiator
{
    public function __construct(
        private Reflector $reflector = new Reflector(),
        private ParameterBuilder $parameterBuilder = new ParameterBuilder(),
    ) {
    }

    /**
     * @template TService of object
     * @template TArgument
     *
     * @param array<TArgument> $arguments
     *
     * @return TService
     * @param Closure(): void $function
     */
    public function buildParameters(
        ContainerInterface $container,
        Closure $function,
        array $arguments = []
    ): array {
        $parameters = $this->reflector
            ->reflectFunction($function)
            ->getParameters();

        return $this->parameterBuilder->build(
            $container,
            $parameters,
            $arguments
        );
    }

    /**
     * @template TService of object
     * @template TArgument
     *
     * @param array<TArgument> $arguments
     *
     * @return TService
     * @param Closure(): void $function
     */
    private function buildClassParameters(
        ContainerInterface $container,
        string $class,
        array $arguments = []
    ): array {
        $reflectionClass =  $this->cachedReflectionClass($container, $class);

        if (!$reflectionClass->isInstantiable()) {
            throw new ClassNotInstantiableException($class);
        }

        return [
            $reflectionClass,
            $this->parameterBuilder->build(
                $container,
                $reflectionClass->getConstructor()?->getParameters() ?? [],
                $arguments
            )
        ];
    }

    /**
     * @template TService of object
     * @template TArgument
     *
     * @param class-string<TService> $class
     * @param array<TArgument>       $arguments
     *
     * @return TService
     */
    public function instantiate(
        ContainerInterface $container,
        string $class,
        array $arguments = []
    ): object {
        [$reflectionClass, $parameters] = $this->buildClassParameters($container, $class, $arguments);

        try {
            return $reflectionClass->newInstance(...$parameters);
        } catch (Throwable $throwable) {
            throw new InstantiatorException($throwable->getMessage(), 0, $throwable);
        }
    }


    private function cachedReflectionClass(ContainerInterface $container, string $class): ReflectionClass
    {
        $cacheKey = sprintf('%s\%s', ReflectionClass::class, $class);

        if ($container->has($cacheKey)) {
            return $container->get($cacheKey);
        }

        $reflectionClass = $this->reflector->reflectClass($class);

        $container->set($cacheKey, $reflectionClass);

        return $reflectionClass;
    }
}
