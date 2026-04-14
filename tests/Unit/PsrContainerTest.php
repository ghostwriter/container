<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\PsrContainer;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Provider\ComposerDefinitionProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;

#[CoversClass(PsrContainer::class)]
#[UsesClass(ComposerDefinitionProvider::class)]
#[UsesClass(ComposerExtraDefinition::class)]
#[UsesClass(Container::class)]
#[UsesClass(ContainerProvider::class)]
final class PsrContainerTest extends AbstractTestCase
{
    public function testImplementsPsrContainerInterface(): void
    {
        self::assertInstanceOf(ContainerInterface::class, new PsrContainer($this->container));
    }
}
