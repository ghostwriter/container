<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

class UnionTypehintWithDefaultValue
{
    private int|float $number;

    public function __construct(int|float $number = 0)
    {
        $this->number = $number;
    }

    public function getNumber(): int|float
    {
        return $this->number;
    }
}
