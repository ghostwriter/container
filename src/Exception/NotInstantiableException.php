<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use RuntimeException as PHPRuntimeException;

final class NotInstantiableException extends PHPRuntimeException implements ContainerExceptionInterface
{
}
