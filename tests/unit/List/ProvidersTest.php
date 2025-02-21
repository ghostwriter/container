<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
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
use Ghostwriter\Container\Name\Extension as ExtensionName;
use Ghostwriter\Container\Name\Factory as FactoryName;
use Ghostwriter\Container\Name\Provider as ProviderName;
use Ghostwriter\Container\Name\Service as ServiceName;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\ServiceProvider\FoobarServiceProvider;
use Tests\Fixture\ServiceProvider\FoobarWithDependencyServiceProvider;
use Tests\Unit\AbstractTestCase;

#[CoversClass(Alias::class)]
#[CoversClass(Aliases::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Container::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extension::class)]
#[CoversClass(ExtensionName::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Factory::class)]
#[CoversClass(FactoryName::class)]
#[CoversClass(Inject::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Provider::class)]
#[CoversClass(ProviderName::class)]
#[CoversClass(Providers::class)]
#[CoversClass(ServiceName::class)]
#[CoversClass(Tags::class)]
final class ProvidersTest extends AbstractTestCase
{
    public function testAdd(): void
    {
        $providers = Providers::new();

        self::assertFalse($providers->has(FoobarWithDependencyServiceProvider::class));
        self::assertFalse($this->container->has(FoobarServiceProvider::class));

        $providers->add(FoobarWithDependencyServiceProvider::class, $this->container);
        $providers->add(FoobarServiceProvider::class, $this->container);

        self::assertTrue($providers->has(FoobarWithDependencyServiceProvider::class));
        self::assertTrue($this->container->has(FoobarServiceProvider::class));
    }
}
