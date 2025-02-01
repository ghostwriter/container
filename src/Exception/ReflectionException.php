<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Interface\ExceptionInterface;

final class ReflectionException extends \ReflectionException implements ExceptionInterface {}
