<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use InvalidArgumentException;

final class CircularDependencyException extends InvalidArgumentException implements ContainerExceptionInterface
{
}
