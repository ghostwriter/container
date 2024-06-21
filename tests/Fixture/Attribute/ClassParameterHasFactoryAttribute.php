<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute;

use Ghostwriter\Container\Attribute\Factory;
use Tests\Fixture\Factory\FoobarFactory;
use Tests\Fixture\Foobar;

final readonly class ClassParameterHasFactoryAttribute
{
    public function __construct(
        #[Factory(FoobarFactory::class)]
        private Foobar $foobar
    ) {
    }

    public function foobar(): Foobar
    {
        return $this->foobar;
    }
}
