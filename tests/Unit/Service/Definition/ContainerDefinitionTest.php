<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Definition;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\PsrContainer;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Definition\ContainerDefinition;
use Ghostwriter\Container\Service\Provider\ComposerDefinitionProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Tests\Unit\AbstractTestCase;

#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClass(ContainerProvider::class)]
#[CoversClass(ComposerDefinitionProvider::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
final class ContainerDefinitionTest extends AbstractTestCase
{
    public function testContainerDefinition(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects(self::once())
            ->method('alias')
            ->with(PsrContainerInterface::class, PsrContainer::class)
            ->seal();

        ($this->container->get(ContainerDefinition::class))($container);
    }

    public function testContainerDefinitionImplementsDefinitionInterface(): void
    {
        self::assertInstanceOf(DefinitionInterface::class, $this->container->get(ContainerDefinition::class));
    }
}
