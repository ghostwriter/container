<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Interface\ExceptionInterface;
use RuntimeException;

final class DontSerializeContainerException extends RuntimeException implements ExceptionInterface {}
