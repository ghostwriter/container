<?php

namespace Tests\Fixture\Attribute\Inject;

use Ghostwriter\Container\Attribute\Inject;
use Tests\Fixture\Attribute\Factory\Foobar2;
use Tests\Fixture\Attribute\Foobar2Interface;

final readonly class ClassHasInjectAttribute
{
    public function __construct(
        #[Inject(Foobar2::class)]
        private Foobar2Interface $foobar
    ) {}

    public function foobar(): Foobar2Interface
    {
        return $this->foobar;
    }
}
