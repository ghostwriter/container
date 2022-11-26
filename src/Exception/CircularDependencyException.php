<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use RuntimeException as PHPRuntimeException;
use Throwable;

final class CircularDependencyException extends PHPRuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $class, array $dependencies, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(
            'Circular dependency: %s -> %s',
            implode(' -> ', $dependencies),
            $class
        ), $code, $previous);
    }
}
