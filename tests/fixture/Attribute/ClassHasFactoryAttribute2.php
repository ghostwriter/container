<?php

namespace Tests\Fixture\Attribute;

use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;

final readonly class ClassHasFactoryAttribute2
{
    public function __construct(
        #[Inject(Foobar2::class)]
        #[Factory(Foobar2Factory::class)]
        private Foobar2Interface $foobar
    )
    {
    }

    public function foobar(): Foobar2Interface
    {
        return $this->foobar;
    }
}
