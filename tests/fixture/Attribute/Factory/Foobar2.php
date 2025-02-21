<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute\Factory;

use Ghostwriter\Container\Attribute\Factory;
use Tests\Fixture\Attribute\Foobar2Interface;
use Tests\Fixture\Factory\Foobar2Factory;

#[Factory(Foobar2Factory::class)]
final readonly class Foobar2 implements Foobar2Interface
{
    public function __construct(private string $foo)
    {
    }

    public function foo(): string
    {
        return $this->foo;
    }
}
