<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

class Bar
{
    public Foo $foo;

    public function __construct(Foo $foo)
    {
        $this->foo = $foo;
    }

    public function foo(): Foo
    {
        return $this->foo;
    }
}
