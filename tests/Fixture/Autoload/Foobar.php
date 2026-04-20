<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Tests\Fixture\Factory\FoobarFactory;

final class Foobar
{
    public function __construct(
        public int $count,
    ) {
    }
}
