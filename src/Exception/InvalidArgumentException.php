<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Interface\ContainerExceptionInterface;

final class InvalidArgumentException extends \InvalidArgumentException implements ContainerExceptionInterface {}
