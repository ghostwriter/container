<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Name;

use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Interface\NameInterface;

use function class_exists;
use function enum_exists;
use function interface_exists;
use function mb_trim;
use function trait_exists;

final readonly class Service implements NameInterface
{
    public function __construct(
        private string $name
    ) {
        if (mb_trim($name) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if (
            ! class_exists($name)
            && ! interface_exists($name)
            && ! trait_exists($name)
            && ! enum_exists($name)
        ) {
            throw new ServiceNotFoundException($name);
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
