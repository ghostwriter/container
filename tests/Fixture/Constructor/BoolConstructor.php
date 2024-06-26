<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class BoolConstructor
{
    public bool $result;

    public function __construct(bool $value)
    {
        $this->result = $value;
    }

    public function value(): bool
    {
        return $this->result;
    }
}
