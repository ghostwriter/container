<?php

declare(strict_types=1);

namespace Tests\Fixture;

final readonly class UnionTypehintWithoutDefaultValue
{
    private float|int $number;

    public function __construct(float|int $number)
    {
        $this->number = $number;
    }

    public function value(): float|int
    {
        return $this->number;
    }
}
