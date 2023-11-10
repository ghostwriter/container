<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Closure;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Throwable;

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
     * @param class-string<TService> $class
     * @param array<TArgument> $arguments
     *
     * @return TService
     */
    public function buildParameters(
        ContainerInterface $container,
        Closure $function,
        array $arguments = []
    ): array {
        $parameters = $this->reflector
            ->reflectFunction($function)->getParameters();

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
     * @param class-string<TService> $class
     * @param array<TArgument> $arguments
     *
     * @return TService
     */
    public function instantiate(
        ContainerInterface $container,
        string $class,
        array $arguments = []
    ): object {
        $classReflection = $this->reflector->reflectClass($class);

        if (!$classReflection->isInstantiable()) {
            throw new ClassNotInstantiableException($class);
        }

        $parameters = $this->parameterBuilder->build(
            $container,
            $classReflection->getConstructor()?->getParameters() ?? [],
            $arguments
        );

        try {
            return $classReflection->newInstance(...$parameters);
        } catch (Throwable $throwable) {
            throw new InstantiatorException($throwable->getMessage());
        }
    }
}
