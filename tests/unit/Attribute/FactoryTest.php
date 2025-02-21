<?php

declare(strict_types=1);

namespace Tests\Unit\Attribute;

use Generator;
use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
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
use Ghostwriter\Container\Name\Factory as FactoryName;
use Ghostwriter\Container\Name\Service as ServiceName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Fixture\Attribute\Factory\ClassHasFactoryAttribute;
use Tests\Fixture\Attribute\Factory\ClassParameterHasClassWithFactoryAttribute;
use Tests\Fixture\Dummy;
use Tests\Fixture\Factory\DummyFactory;
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
#[CoversClass(Instances::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Tags::class)]
#[CoversClass(ServiceName::class)]
#[CoversClass(FactoryName::class)]
#[CoversClass(Alias::class)]
final class FactoryTest extends AbstractTestCase
{
    #[DataProvider('provide')]
    public function testClassHasFactoryAttribute(string $class): void
    {
        self::assertInstanceOf($class, $this->container->get($class));
    }

    public function testClassHasFactoryAttribute2(): void
    {
        self::assertInstanceOf(DummyFactory::class, $this->container->get(Dummy::class)->getDummyFactory());
    }

    public function testClassParameterHasFactoryAttribute(): void
    {
        self::assertInstanceOf(
            Foobar::class,
            $this->container->get(ClassParameterHasClassWithFactoryAttribute::class)->foobar()
        );
    }

    public static function provide(): Generator
    {
        foreach ([Dummy::class, ClassHasFactoryAttribute::class] as $class) {
            yield $class => [$class];
        }
    }
}
