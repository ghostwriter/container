<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute;

use Ghostwriter\Container\Attribute\Extension;
use RuntimeException;
use Tests\Fixture\Extension\ClassHasExtensionAttributeExtension;
use Tests\Fixture\Foobar;

final class ClassRequiresExtensionAttribute
{
    public function __construct(
        private ?Foobar $foobar = null
    ) {
    }

    public function foobar(): Foobar
    {
        if ($this->foobar === null) {
            throw new RuntimeException('Foobar is null');
        }

        return $this->foobar;
    }

    public function setFoobar(Foobar $foobar): void
    {
        $this->foobar = $foobar;
    }
}
