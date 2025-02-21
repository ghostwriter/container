<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute\Inject;

use Ghostwriter\Container\Attribute\Inject;
use Tests\Fixture\Attribute\Factory\Foobar2;
use Tests\Fixture\Attribute\Foobar2Interface;

final readonly class InvokableClassHasInjectAttribute
{
    public function __invoke(
        #[Inject(Foobar2::class)]
        Foobar2Interface $foobar
    ): Foobar2Interface
    {
        return $foobar;
    }
}
