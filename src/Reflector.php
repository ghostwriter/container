<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Exception\ReflectorException;
use ReflectionClass;
use ReflectionFunction;
use Throwable;

/** @see \Ghostwriter\ContainerTests\Unit\ReflectorTest */
final readonly class Reflector
{
    /**
     * @template TClass of object
     *
     * @param class-string<TClass> $class
     *
     * @throws ReflectorException
     *
     * @return ReflectionClass<TClass>
     */
    public function reflectClass(string $class): ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (Throwable $throwable) {
            throw new ReflectorException($throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * @param callable|callable-string $function
     *
     * @throws ReflectorException
     */
    public function reflectFunction(callable|string $function): ReflectionFunction
    {
        try {
            return new ReflectionFunction($function);
        } catch (Throwable $throwable) {
            throw new ReflectorException($throwable->getMessage(), 0, $throwable);
        }
    }

    public static function new(): self
    {
        return new self();
    }
}
