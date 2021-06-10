<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use LogicException as PHPLogicException;

use function sprintf;

final class LogicException extends PHPLogicException implements ContainerExceptionInterface
{
    public static function dontClone(string $class): self
    {
        return new self(sprintf('Please do not clone "%s" instances.', $class));
    }

    public static function dontSerialize(string $class): self
    {
        return new self(sprintf('Please do not serialize "%s" instances.', $class));
    }

    public static function dontUnserialize(string $class): self
    {
        return new self(sprintf('Please do not unserialize "%s" instances.', $class));
    }

    public static function serviceAlreadyRegistered(string $id): self
    {
        return new self(sprintf('Service "%s" has already been registered.', $id));
    }

    public static function serviceCannotAliasItself(string $id): self
    {
        return new self(sprintf('Service "%s" cannot alias itself.', $id));
    }

    public static function serviceProviderAlreadyRegistered(string $id): self
    {
        return new self(sprintf('Service provider "%s" has already been registered.', $id));
    }

    public static function serviceExtensionAlreadyRegistered(string $id): self
    {
        return new self(sprintf('Service extension "%s" has already been registered.', $id));
    }

    public static function serviceTagAlreadyRegistered(string $tag, string $id): self
    {
        return new self(sprintf('Service tag "%s" has already been registered for service "%s".', $tag, $id));
    }
}
