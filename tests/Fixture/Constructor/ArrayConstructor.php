<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\Constructor;

class ArrayConstructor
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
