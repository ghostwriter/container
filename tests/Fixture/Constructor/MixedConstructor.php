<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\Constructor;

class MixedConstructor
{
    public mixed $result;

    public function __construct(mixed $value)
    {
        $this->result = $value;
    }

    public function value(): mixed
    {
        return $this->result;
    }
}
