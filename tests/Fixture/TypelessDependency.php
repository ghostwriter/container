<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture;

class TypelessDependency
{
    /** @var mixed */
    public $value;

    /** @param mixed $value */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /** @param mixed $value */
    public function value()
    {
        return $this->value;
    }
}
