<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Tests\Unit\ReflectorTest;
use ReflectionClass;
use Throwable;

/** @see ReflectorTest */
final class Reflector
{
    /**
     * @template TClass of object
     *
     * @param class-string<TClass> $class
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
}
