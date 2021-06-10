<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\Constructor;

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
