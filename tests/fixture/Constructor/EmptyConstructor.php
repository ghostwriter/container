<?php

declare(strict_types=1);

namespace Tests\Fixture\Constructor;

final class EmptyConstructor
{
    public function __construct()
    {
    }

    public function value(): null
    {
        return null;
    }
}
