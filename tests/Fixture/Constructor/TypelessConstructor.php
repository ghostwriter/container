<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\Constructor;

class TypelessConstructor
{
    /** @var mixed */
    public $result;

    /** @param mixed $value */
    public function __construct($value)
    {
        $this->result = $value;
    }

    /** @return mixed */
    public function value()
    {
        return $this->result;
    }
}
