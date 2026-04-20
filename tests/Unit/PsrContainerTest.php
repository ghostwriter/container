<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\PsrContainer;
use Ghostwriter\Container\Service\Provider\ComposerServiceProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface as PsrContainerInterface;

#[CoversClass(PsrContainer::class)]
#[UsesClass(ComposerServiceProvider::class)]
#[UsesClass(Container::class)]
#[UsesClass(ContainerProvider::class)]
final class PsrContainerTest extends AbstractTestCase
{
    public function testGet(): void
    {
        $psrContainer = new PsrContainer($this->container);

        self::assertSame($this->container, $psrContainer->get(ContainerInterface::class));
    }

    public function testHas(): void
    {
        $psrContainer = new PsrContainer($this->container);

        self::assertTrue($psrContainer->has(ContainerInterface::class));

        self::assertFalse($psrContainer->has('nonexistent'));
    }

    public function testImplementsPsrContainerInterface(): void
    {
        self::assertInstanceOf(PsrContainerInterface::class, new PsrContainer($this->container));
    }
}
