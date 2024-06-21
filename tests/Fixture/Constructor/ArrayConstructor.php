<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class ArrayConstructor
{
    public array $result;

    public function __construct(array $value)
    {
        $this->result = $value;
    }

    public function value(): array
    {
        return $this->result;
    }
}
