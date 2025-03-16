<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Tests\Fixture\Factory\DummyFactory;

final class ClassWithArray
{
    public function __construct(
        public DummyFactory $dummyFactory,
        public array $items = [],
    ) {
    }
}
