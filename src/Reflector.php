<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Tests\Unit\ReflectorTest;
use ReflectionClass;
use ReflectionFunction;
use Throwable;

/** @see ReflectorTest */
final class Reflector
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
    public static function getReflectionClass(string $class): ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (Throwable $throwable) {
            throw new ReflectorException($throwable->getMessage());
        }
    }

    /**
     * @param callable|callable-string $function
     *
     * @throws ReflectorException
     */
    public static function getReflectionFunction(callable|string $function): ReflectionFunction
    {
        try {
            return new ReflectionFunction($function);
        } catch (Throwable $throwable) {
            throw new ReflectorException($throwable->getMessage());
        }
    }
}
