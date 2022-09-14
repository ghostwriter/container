<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;

use InvalidArgumentException;

final class ServiceAliasMustBeNonEmptyStringException extends InvalidArgumentException implements ContainerExceptionInterface
{
}
