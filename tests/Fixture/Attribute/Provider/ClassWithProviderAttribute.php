<?php

namespace Tests\Fixture\Attribute\Provider;

use Ghostwriter\Container\Attribute\Provider;
use Tests\Fixture\Dummy;
use Tests\Fixture\ServiceProvider\FoobarWithDependencyServiceProvider;

#[Provider(FoobarWithDependencyServiceProvider::class)]
final readonly class ClassWithProviderAttribute {
    public function __construct(
        private Dummy $dummy
    ) {
    }

    public function getDummy(): Dummy {
        return $this->dummy;
    }
}
