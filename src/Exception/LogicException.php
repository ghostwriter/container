<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use LogicException as PHPLogicException;

use function sprintf;

final class LogicException extends PHPLogicException implements ContainerExceptionInterface
{
    public static function serviceAlreadyRegistered(string $id): self
    {
        return new self(sprintf('Service "%s" has already been registered.', $id));
    }

    public static function serviceCannotAliasItself(string $id): self
    {
        return new self(sprintf('Service "%s" cannot alias itself.', $id));
    }

    public static function serviceExtensionAlreadyRegistered(string $id): self
    {
        return new self(sprintf('Service extension "%s" has already been registered.', $id));
    }

    public static function serviceProviderAlreadyRegistered(string $id): self
    {
        return new self(sprintf('Service provider "%s" has already been registered.', $id));
    }
}
