<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\Constructor;

class OptionalConstructor
{
    /** @var mixed */
    public $result;

    /** @param null|mixed $value */
    public function __construct($value = null)
    {
        $this->result = $value;
    }

    /** @return mixed */
    public function value()
    {
        return $this->result;
    }
}
