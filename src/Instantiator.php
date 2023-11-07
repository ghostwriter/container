<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Interface\ContainerInterface;
use ReflectionClass;
use Throwable;

final readonly class Instantiator
{
    /**
     * @template TService of object
     * @template TArgument
     *
     * @param ReflectionClass<TService> $class
     * @param array<TArgument> $arguments
     *
     * @return TService
     */
    public function instantiate(
        ReflectionClass $class,
        array $arguments = []
    ): object {
        if (!$class->isInstantiable()) {
            throw new ClassNotInstantiableException($class->getName());
        }

        try {
            return $class->newInstance(...$arguments);
        } catch (Throwable $throwable) {
            throw new InstantiatorException($throwable->getMessage());
        }
    }
}
