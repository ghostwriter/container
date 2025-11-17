<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Definition;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Definition\ContainerDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;

#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(ContainerDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
final class ComposerExtraDefinitionTest extends AbstractTestCase
{
    public function testComposerExtraDefinition(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects(self::once())
            ->method('define')
            ->with(ContainerDefinition::class);

        ($this->container->get(ComposerExtraDefinition::class))($container);
    }

    public function testImplementsDefinitionInterface(): void
    {
        self::assertInstanceOf(DefinitionInterface::class, new ComposerExtraDefinition());
    }
}
