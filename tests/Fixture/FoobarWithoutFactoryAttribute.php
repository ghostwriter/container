<?php

namespace Tests\Fixture;

final class FoobarWithoutFactoryAttribute
{
    public function __construct(
        public int $count,
    ) {}
}
