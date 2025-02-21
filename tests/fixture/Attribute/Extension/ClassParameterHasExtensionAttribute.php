<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute\Extension;

use Ghostwriter\Container\Attribute\Extension;
use Tests\Fixture\Extension\ClassRequiresExtensionAttributeExtension;
use Tests\Fixture\Foobar;

final readonly class ClassParameterHasExtensionAttribute
{

    public function __construct(
        private ClassRequiresExtensionAttribute $foobar
    ) {
    }

    public function foobar(): Foobar
    {
        return $this->foobar->foobar();
    }
}
