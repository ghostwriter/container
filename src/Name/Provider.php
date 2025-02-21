<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Name;

use Ghostwriter\Container\Exception\ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException;
use Ghostwriter\Container\Interface\NameInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;

use function is_a;

final readonly class Provider implements NameInterface
{
    public function __construct(
        private string $name
    ) {
        if (! is_a($name, ServiceProviderInterface::class, true)) {
            throw new ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException($name);
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
