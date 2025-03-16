<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute\Factory;

use Tests\Fixture\Foobar;

final readonly class ClassParameterHasClassWithFactoryAttribute
{
    public function __construct(
        private Foobar $foobar
    ) {
    }

    public function foobar(): Foobar
    {
        return $this->foobar;
    }
}
