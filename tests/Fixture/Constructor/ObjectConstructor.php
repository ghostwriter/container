<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\Constructor;

class ObjectConstructor
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
