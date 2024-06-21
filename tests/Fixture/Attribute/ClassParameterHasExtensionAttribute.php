<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Tests\Fixture\Extension\ClassRequiresExtensionAttributeExtension;
use Tests\Fixture\Factory\FoobarFactory;
use Tests\Fixture\Foobar;

final readonly class ClassParameterHasExtensionAttribute
{

    public function __construct(
        #[Extension(ClassRequiresExtensionAttributeExtension::class)]
        private ClassRequiresExtensionAttribute $foobar
    ) {
    }

    public function foobar(): Foobar
    {
        return $this->foobar->foobar();
    }
}
