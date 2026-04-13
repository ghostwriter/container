<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Provider;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\Service\Provider\ComposerDefinitionProviderInterface;
use Ghostwriter\Container\Interface\Service\ProviderInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Provider\ComposerDefinitionProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\AbstractTestCase;

#[CoversClass(ComposerDefinitionProvider::class)]
#[UsesClass(ComposerExtraDefinition::class)]
#[UsesClass(Container::class)]
#[UsesClass(ContainerProvider::class)]
final class ComposerDefinitionProviderTest extends AbstractTestCase
{
    public function testImplementsDefinitionProviderInterface(): void
    {
        self::assertInstanceOf(
            ComposerDefinitionProviderInterface::class,
            new ComposerDefinitionProvider($this->container)
        );
    }

    public function testImplementsProviderInterface(): void
    {
        self::assertInstanceOf(ProviderInterface::class, new ComposerDefinitionProvider($this->container));
    }
}
