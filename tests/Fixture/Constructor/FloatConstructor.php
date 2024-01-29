<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\Constructor;

class FloatConstructor
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
