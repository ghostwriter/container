<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use RuntimeException;

final class ReflectorException extends RuntimeException implements ContainerExceptionInterface
{
}
