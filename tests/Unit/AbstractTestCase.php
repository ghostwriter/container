<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\ContainerNotFoundExceptionInterface;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Override;
use Throwable;

abstract class AbstractTestCase extends MockeryTestCase
{
    protected ContainerInterface $container;

    #[Override]
    final protected function mockeryTestSetUp(): void
    {
        parent::mockeryTestSetUp();

        $this->container = Container::getInstance();
        $this->container->reset();
    }

    /** @param class-string<Throwable> $expected */
    final public function assertException(string $expected): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException($expected);
    }

    /** @param class-string<Throwable> $expected */
    final public function assertNotFoundException(string $expected): void
    {
        $this->expectException(ContainerNotFoundExceptionInterface::class);
        $this->assertException($expected);
    }
}
