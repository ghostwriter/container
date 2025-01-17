<?php

declare(strict_types=1);

namespace Tests\Unit\Attribute;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\Attribute\ClassHasFactoryAttribute;
use Tests\Fixture\Attribute\ClassParameterHasFactoryAttribute;
use Tests\Fixture\Foobar;
use Tests\Unit\AbstractTestCase;

#[CoversClass(Aliases::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Container::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extension::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Factory::class)]
#[CoversClass(Inject::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Tags::class)]
final class FactoryTest extends AbstractTestCase
{
    public function testClassHasFactoryAttribute(): void
    {
        self::assertInstanceOf(Foobar::class, $this->container->get(ClassHasFactoryAttribute::class)->foobar());
    }

    public function testClassParameterHasFactoryAttribute(): void
    {
        self::assertInstanceOf(
            Foobar::class,
            $this->container->get(ClassParameterHasFactoryAttribute::class)->foobar()
        );
    }
}
