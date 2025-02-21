<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Name;

use Ghostwriter\Container\Exception\ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException;
use Ghostwriter\Container\Interface\FactoryInterface;
use Ghostwriter\Container\Interface\NameInterface;

use function is_a;

final readonly class Factory implements NameInterface
{
    public function __construct(
        private string $name,
    ) {
        if (! is_a($name, FactoryInterface::class, true)) {
            throw new ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException($name);
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
