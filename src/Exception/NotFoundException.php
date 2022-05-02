<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use RuntimeException as PHPRuntimeException;
use function sprintf;

final class NotFoundException extends PHPRuntimeException implements NotFoundExceptionInterface
{
    public static function notRegistered(string $id): self
    {
        return new self(sprintf('Service "%s" has not been registered.', $id));
    }
}
