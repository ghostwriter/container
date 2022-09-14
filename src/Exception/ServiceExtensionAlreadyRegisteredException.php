<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use LogicException;

final class ServiceExtensionAlreadyRegisteredException extends LogicException implements ContainerExceptionInterface
{
}
