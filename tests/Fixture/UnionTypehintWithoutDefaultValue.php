<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

class UnionTypehintWithoutDefaultValue
{
    private int|float $number;

    public function __construct(int|float $number)
    {
        $this->number = $number;
    }

    public function value(): int|float
    {
        return $this->number;
    }
}
