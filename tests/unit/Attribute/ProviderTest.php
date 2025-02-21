<?php

declare(strict_types=1);

namespace Tests\Unit\Attribute;

use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Provider;
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
use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tests\Fixture\Attribute\Provider\ClassWithProviderAttribute;
use Tests\Fixture\Dummy;

#[CoversClass(Factory::class)]
#[CoversClass(Container::class)]
#[CoversClass(Aliases::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Alias::class)]
#[CoversClass(\Ghostwriter\Container\Name\Factory::class)]
#[CoversClass(\Ghostwriter\Container\Name\Provider::class)]
#[CoversClass(Tags::class)]
#[CoversClass(Service::class)]
#[CoversClass(Provider::class)]
final class ProviderTest extends TestCase
{
    public function test(): void
    {
        $container = Container::getInstance();

        self::assertInstanceof(Dummy::class, $container->get(ClassWithProviderAttribute::class)->getDummy());
    }
}
