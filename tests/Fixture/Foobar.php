<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Ghostwriter\Container\Attribute\Factory;
use Tests\Fixture\Factory\FoobarFactory;

#[Factory(FoobarFactory::class)]
final class Foobar
{
    public function __construct(
        public int $count,
    ) {
    }
}
