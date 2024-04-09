<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\InstantiatorException;
use Throwable;

/** @see \Ghostwriter\ContainerTests\Unit\InstantiatorTest */
final readonly class Instantiator
{
    public function __construct(
        public Reflector $reflector,
        public ParameterBuilder $parameterBuilder,
    ) {}

    /**
     * @template TService of object
     * @template TArgument
     *
     * @param class-string<TService> $class
     * @param array<TArgument>       $arguments
     *
     * @return TService
     */
    public function instantiate(string $class, array $arguments = []): object
    {
        $reflectionClass = $this->reflector->reflectClass($class);

        if (! $reflectionClass->isInstantiable()) {
            throw new ClassNotInstantiableException($class);
        }

        $parameters = $this->parameterBuilder->build(
            $reflectionClass->getConstructor()?->getParameters() ?? [],
            $arguments
        );

        try {
            return $reflectionClass->newInstance(...$parameters);
        } catch (Throwable $throwable) {
            throw new InstantiatorException($throwable->getMessage(), 0, $throwable);
        }
    }

    public static function new(Reflector $reflector, ParameterBuilder $parameterBuilder): self
    {
        return new self($reflector, $parameterBuilder);
    }
}
