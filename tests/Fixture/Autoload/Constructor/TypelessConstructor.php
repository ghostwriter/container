<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class TypelessConstructor
{
    public $result;

    public function __construct($value)
    {
        $this->result = $value;
    }

    public function value()
    {
        return $this->result;
    }
}
