<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

class Foobar
{
    public function __construct(
        public int $count,
    ) {
    }
}
