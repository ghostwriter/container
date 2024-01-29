<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\Constructor;

class IterableConstructor
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
