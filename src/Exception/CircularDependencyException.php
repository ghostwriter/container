<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use RuntimeException as PHPRuntimeException;

final class CircularDependencyException extends PHPRuntimeException implements ContainerExceptionInterface
{
    /**
     * @param class-string        $class
     * @param array<class-string> $dependencies
     */
    public function __construct(string $class, array $dependencies)
    {
        parent::__construct(sprintf('Circular dependency: %s -> %s', implode(' -> ', $dependencies), $class));
    }
}
