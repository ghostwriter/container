<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use RuntimeException as PHPRuntimeException;

use function implode;
use function sprintf;

final class CircularDependencyException extends PHPRuntimeException implements ContainerExceptionInterface
{
    public static function detected(string $class, array $stack): self
    {
        return new self(
            sprintf(
                'Circular dependency: %s -> %s',
                implode(' -> ', $stack),
                $class
            )
        );
    }
}
