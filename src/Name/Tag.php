<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Name;

use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Interface\NameInterface;

use function mb_trim;

final readonly class Tag implements NameInterface
{
    public function __construct(
        private string $name
    ) {
        if (mb_trim($name) === '') {
            throw new ServiceTagMustBeNonEmptyStringException();
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
