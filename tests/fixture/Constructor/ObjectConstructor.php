<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class ObjectConstructor
{
    public object $result;

    public function __construct(object $value)
    {
        $this->result = $value;
    }

    public function value(): object
    {
        return $this->result;
    }
}
