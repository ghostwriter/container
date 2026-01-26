<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class OptionalConstructor
{
    public $result;

    /** @param null|mixed $value */
    public function __construct(mixed $value = null)
    {
        $this->result = $value;
    }

    public function value()
    {
        return $this->result;
    }
}
