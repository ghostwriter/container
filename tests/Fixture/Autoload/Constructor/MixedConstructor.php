<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class MixedConstructor
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
