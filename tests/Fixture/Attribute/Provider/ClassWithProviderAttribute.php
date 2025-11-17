<?php

namespace Tests\Fixture\Attribute\Provider;

use Definition\FoobarWithDependencyDefinition;
use Ghostwriter\Container\Attribute\Provider;
use Tests\Fixture\Dummy;

#[Provider(FoobarWithDependencyDefinition::class)]
final readonly class ClassWithProviderAttribute {
    public function __construct(
        private Dummy $dummy
    ) {
    }

    public function getDummy(): Dummy {
        return $this->dummy;
    }
}
