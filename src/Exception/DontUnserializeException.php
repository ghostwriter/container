<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use BadMethodCallException;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;

final class DontUnserializeException extends BadMethodCallException implements ContainerExceptionInterface
{
}
