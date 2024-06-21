<?php

declare(strict_types=1);

namespace Tests\Fixture;

final class TypelessDependency
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }
}
