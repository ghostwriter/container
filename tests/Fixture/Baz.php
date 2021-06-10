<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

class Baz
{
    public Bar $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }

    public function bar(): Bar
    {
        return $this->bar;
    }

    public function foo(): Foo
    {
        return $this->bar->foo();
    }
}
