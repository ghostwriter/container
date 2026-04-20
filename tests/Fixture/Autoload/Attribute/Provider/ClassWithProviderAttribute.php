<?php

namespace Tests\Fixture\Attribute\Provider;

use Tests\Fixture\Dummy;

final readonly class ClassWithProviderAttribute {
    public function __construct(
        private Dummy $dummy
    ) {
    }

    public function getDummy(): Dummy {
        return $this->dummy;
    }
}
