<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Provider;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\Service\Provider\ComposerServiceProviderInterface;
use Ghostwriter\Container\Interface\Service\ProviderInterface;
use Ghostwriter\Container\Service\Provider\ComposerServiceProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Unit\AbstractTestCase;

#[CoversClass(ComposerServiceProvider::class)]
#[UsesClass(Container::class)]
#[UsesClass(ContainerProvider::class)]
final class ComposerServiceProviderTest extends AbstractTestCase
{
    public function testImplementsDefinitionProviderInterface(): void
    {
        self::assertInstanceOf(
            ComposerServiceProviderInterface::class,
            new ComposerServiceProvider($this->container)
        );
    }

    public function testImplementsProviderInterface(): void
    {
        self::assertInstanceOf(ProviderInterface::class, new ComposerServiceProvider($this->container));
    }
}
