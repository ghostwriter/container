<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Provider;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Service\Provider\AbstractProvider;
use Ghostwriter\Container\Service\Provider\ComposerServiceProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Fixture\Provider\BootProviderMock;
use Tests\Fixture\Provider\RegisterProviderMock;
use Tests\Unit\AbstractTestCase;

#[CoversClass(AbstractProvider::class)]
#[UsesClass(ComposerServiceProvider::class)]
#[UsesClass(Container::class)]
#[UsesClass(ContainerProvider::class)]
final class AbstractProviderTest extends AbstractTestCase
{
    public function testBootIsNoOp(): void
    {
        $bootProviderMock = new BootProviderMock();
        $container = $this->createMock(ContainerInterface::class);

        $container->expects(self::never())
            ->method('get')
            ->seal();

        self::assertNull($bootProviderMock->boot($container));
    }

    public function testRegisterIsNoOp(): void
    {
        $registerProviderMock = new RegisterProviderMock();
        $builder = $this->createMock(BuilderInterface::class);

        $builder->expects(self::never())
            ->method('alias')
            ->seal();

        self::assertNull($registerProviderMock->register($builder));
    }
}
