<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use RuntimeException;

final class Reflector
{
    /**
     * @template TObject of object
     *
     * @var array<class-string<TObject>,ReflectionClass<TObject>>
     */
    private array $cache = [];

    public function reflect(string $class): ReflectionClass
    {
        try {
            $reflectionClass = $this->cache[$class] ??= new ReflectionClass($class);

            if (! $reflectionClass->isInstantiable()) {
                throw new ReflectorException(sprintf('Class "%s" is not instantiable.', $class));
            }
        } catch (ReflectionException) {
            throw new ReflectorException(sprintf('Class "%s" does not exist.', $class));
        }

        return $reflectionClass;
    }
}
