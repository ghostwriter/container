<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\Constructor;

class CallableConstructor
{
    /** @var callable */
    public $result;

    public function __construct(callable $value)
    {
        $this->result = $value;
    }

    public function value(): callable
    {
        return $this->result;
    }
}
