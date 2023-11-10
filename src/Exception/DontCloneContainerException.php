<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use RuntimeException;

final class DontCloneContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
