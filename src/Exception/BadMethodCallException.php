<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use BadMethodCallException as PHPBadMethodCallException;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;

use function sprintf;

final class BadMethodCallException extends PHPBadMethodCallException implements ContainerExceptionInterface
{
    public static function dontClone(string $class): self
    {
        return new self(sprintf('"%s" is not cloneable.', $class));
    }

    public static function dontSerialize(string $class): self
    {
        return new self(sprintf('"%s" is not serializable.', $class));
    }

    public static function dontUnserialize(string $class): self
    {
        return new self(sprintf('"%s" is not unserializable.', $class));
    }
}
