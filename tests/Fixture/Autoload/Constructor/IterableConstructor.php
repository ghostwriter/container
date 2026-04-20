<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class IterableConstructor
{
    public iterable $result;

    public function __construct(iterable $value)
    {
        $this->result = $value;
    }

    public function value(): iterable
    {
        return $this->result;
    }
}
