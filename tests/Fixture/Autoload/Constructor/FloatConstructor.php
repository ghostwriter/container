<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class FloatConstructor
{
    public float $result;

    public function __construct(float $value)
    {
        $this->result = $value;
    }

    public function value(): float
    {
        return $this->result;
    }
}
