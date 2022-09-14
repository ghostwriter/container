<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use RuntimeException;

final class UnresolvableParameterException extends RuntimeException implements ContainerExceptionInterface
{
}
