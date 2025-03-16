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
use Ghostwriter\Container\Name\Alias;
use Ghostwriter\Container\Name\Extension as ExtensionName;
use Ghostwriter\Container\Name\Factory as FactoryName;
use Ghostwriter\Container\Name\Service as ServiceName;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\Attribute\Extension\ClassHasExtensionAttribute;
use Tests\Fixture\Attribute\Extension\ClassParameterHasExtensionAttribute;
use Tests\Fixture\Foobar;
use Tests\Unit\AbstractTestCase;
use Throwable;

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
#[CoversClass(ServiceName::class)]
#[CoversClass(FactoryName::class)]
#[CoversClass(Alias::class)]
#[CoversClass(ExtensionName::class)]
final class ExtensionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testClassHasExtensionAttribute(): void
    {
        self::assertInstanceOf(Foobar::class, $this->container->get(ClassHasExtensionAttribute::class)->foobar());
    }

    /**
     * @throws Throwable
     */
    public function testClassParameterHasExtensionAttribute(): void
    {
        self::assertInstanceOf(
            Foobar::class,
            $this->container->get(ClassParameterHasExtensionAttribute::class)->foobar()
        );
    }
}
