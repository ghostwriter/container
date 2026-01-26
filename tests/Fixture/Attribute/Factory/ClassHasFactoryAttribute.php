<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute\Factory;

use Ghostwriter\Container\Attribute\Factory;
use Tests\Fixture\Factory\ClassHasFactoryAttributeFactory;
use Tests\Fixture\Foobar;

#[Factory(ClassHasFactoryAttributeFactory::class)]
final readonly class ClassHasFactoryAttribute
{
    public function __construct(
        private Foobar $foobar
    ) {
    }

    public function foobar(): Foobar
    {
        return $this->foobar;
    }
}

