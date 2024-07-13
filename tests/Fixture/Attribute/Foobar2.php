<?php

namespace Tests\Fixture\Attribute;

final class Foobar2 implements Foobar2Interface
{
    public function __construct(private string $foo)
    {
    }

    public function foo(): string
    {
        return $this->foo;
    }
}
