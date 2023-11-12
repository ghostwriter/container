<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Interface\ExceptionInterface;
use InvalidArgumentException;

final class AliasNameMustBeNonEmptyStringException extends InvalidArgumentException implements ExceptionInterface
{
}
