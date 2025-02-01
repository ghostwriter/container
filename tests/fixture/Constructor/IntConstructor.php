<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class IntConstructor
{
    public int $result;

    public function __construct(int $value)
    {
        $this->result = $value;
    }

    public function value(): int
    {
        return $this->result;
    }
}
