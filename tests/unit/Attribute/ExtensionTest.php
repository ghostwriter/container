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
use Tests\Fixture\Attribute\ClassHasExtensionAttribute;
use Tests\Fixture\Attribute\ClassHasFactoryAttribute2;
use Tests\Fixture\Attribute\ClassParameterHasExtensionAttribute;
use Tests\Fixture\Attribute\Foobar2;
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
final class ExtensionTest extends AbstractTestCase
{
    public function testClassHasExtensionAttribute(): void
    {
        self::assertInstanceOf(Foobar::class, $this->container->get(ClassHasExtensionAttribute::class)->foobar());
        self::assertInstanceOf(Foobar2::class, $this->container->get(ClassHasFactoryAttribute2::class)->foobar());
    }

    public function testClassParameterHasExtensionAttribute(): void
    {
        self::assertInstanceOf(
            Foobar::class,
            $this->container->get(ClassParameterHasExtensionAttribute::class)->foobar()
        );
    }
}
