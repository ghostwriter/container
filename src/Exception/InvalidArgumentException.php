<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use InvalidArgumentException as PHPInvalidArgumentException;
use function sprintf;

final class InvalidArgumentException extends PHPInvalidArgumentException implements ContainerExceptionInterface
{
    public static function emptyServiceAlias(): self
    {
        return new self('Service "Alias" cannot be empty.');
    }

    public static function emptyServiceId(): self
    {
        return new self('Service "ID" cannot be empty.');
    }

    public static function emptyServiceTagForServiceId(string $id): self
    {
        return new self(sprintf('Service "TAG" for Service "%s" cannot be empty.', $id));
    }
}
