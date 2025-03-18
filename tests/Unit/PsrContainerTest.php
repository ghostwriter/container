<?php

declare(strict_types=1);

namespace Tests\Unit;

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
use Ghostwriter\Container\PsrContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface as PsrContainerInterface;

#[CoversClass(Aliases::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Tags::class)]
#[CoversClass(Alias::class)]
#[CoversClass(Service::class)]
#[CoversClass(Container::class)]
#[CoversClass(PsrContainer::class)]
final class PsrContainerTest extends AbstractTestCase
{
    public function testGhostwriterContainerCanInstantiatePsrContainer(): void
    {
        self::assertInstanceOf(PsrContainer::class, $this->container->get(PsrContainerInterface::class));
    }

    public function testImplementsPsrContainerInterface(): void
    {
        self::assertInstanceOf(PsrContainerInterface::class, PsrContainer::new());
    }
}
