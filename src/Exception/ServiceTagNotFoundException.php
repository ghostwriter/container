<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use InvalidArgumentException;

final class ServiceTagNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface {}
