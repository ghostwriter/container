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
use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\Attribute\Factory\Foobar2;
use Tests\Fixture\Attribute\Foobar2Interface;
use Tests\Fixture\Attribute\Inject\ClassHasInjectAttribute;
use Tests\Fixture\Attribute\Inject\ClassParameterHasInjectAttribute;
use Tests\Fixture\Attribute\Inject\InvokableClassHasInjectAttribute;
use Tests\Fixture\GitHubClient;
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
#[CoversClass(Service::class)]
#[CoversClass(Alias::class)]
#[CoversClass(\Ghostwriter\Container\Name\Factory::class)]
final class InjectTest extends AbstractTestCase
{
    public function testClassHasInjectAttribute(): void
    {
        self::assertInstanceOf(
            Foobar2Interface::class,
            $this->container->get(ClassHasInjectAttribute::class)->foobar(),
        );

        $classHasInjectAttribute = $this->container->get(ClassHasInjectAttribute::class);
        self::assertInstanceOf(ClassHasInjectAttribute::class, $classHasInjectAttribute);
        self::assertInstanceOf(Foobar2::class, $classHasInjectAttribute->foobar());
    }

    public function testClassParameterHasInjectAttribute(): void
    {
        self::assertInstanceOf(
            GitHubClient::class,
            $this->container->get(ClassParameterHasInjectAttribute::class)->getClient()
        );
    }

    public function testInvokableClassHasInjectAttribute(): void
    {
        self::assertInstanceOf(Foobar2::class, $this->container->invoke(InvokableClassHasInjectAttribute::class));
    }
}
