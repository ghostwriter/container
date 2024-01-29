<?php

namespace Ghostwriter\ContainerTests\Fixture;

class ClassWithArray
{
    public function __construct(
        public DummyFactory $dummyFactory,
        public array $items = [],
    )
    {
    }
}
