<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\Constructor;

class StringConstructor
{
    public string $result;

    public function __construct(string $value)
    {
        $this->result = $value;
    }

    public function value(): string
    {
        return $this->result;
    }
}
