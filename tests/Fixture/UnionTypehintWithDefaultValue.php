<?php

declare(strict_types=1);

namespace Tests\Fixture;

final readonly class UnionTypehintWithDefaultValue
{
    private float|int $number;

    public function __construct(float|int $number = 0)
    {
        $this->number = $number;
    }

    public function value(): float|int
    {
        return $this->number;
    }
}
