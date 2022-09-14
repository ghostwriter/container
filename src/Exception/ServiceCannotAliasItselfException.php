<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use LogicException as PHPLogicException;

final class ServiceCannotAliasItselfException extends PHPLogicException implements ContainerExceptionInterface
{
}
