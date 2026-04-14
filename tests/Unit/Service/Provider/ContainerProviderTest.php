<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Provider;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\Service\ProviderInterface;
use Ghostwriter\Container\PsrContainer;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Provider\ComposerDefinitionProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use Tests\Unit\AbstractTestCase;

#[CoversClass(ContainerProvider::class)]
#[UsesClass(ComposerDefinitionProvider::class)]
#[UsesClass(ComposerExtraDefinition::class)]
#[UsesClass(Container::class)]
final class ContainerProviderTest extends AbstractTestCase
{
    public function testContainerProvider(): void
    {
        $container = $this->createMock(\Ghostwriter\Container\Interface\ContainerInterface::class);

        $container->expects(self::once())
            ->method('alias')
            ->with(ContainerInterface::class, PsrContainer::class)
            ->seal();

        $containerProvider = new ContainerProvider();

        $containerProvider->register($container);
    }

    public function testImplementsProviderInterface(): void
    {
        self::assertInstanceOf(ProviderInterface::class, new ContainerProvider());
    }
}
