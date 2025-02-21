<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Name;

use Ghostwriter\Container\Exception\ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Container\Interface\NameInterface;

use function is_a;

final readonly class Extension implements NameInterface
{
    public function __construct(
        private string $name,
    ) {
        if (! is_a($name, ExtensionInterface::class, true)) {
            throw new ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException($name);
        }
    }

    public static function new(string $name): self
    {
        return new self($name);
    }

    public function toString(): string
    {
        return $this->name;
    }
}
