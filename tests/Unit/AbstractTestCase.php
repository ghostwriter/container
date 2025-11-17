<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use InvalidArgumentException;
use Mockery;
use Override;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class AbstractTestCase extends TestCase
{
    protected ContainerInterface $container;

    #[Override]
    final protected function setUp(): void
    {
        parent::setUp();

        $this->container = Container::getInstance();
        $this->container->reset();
    }

    /** @throws Throwable */
    #[Override]
    final protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
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
        $this->expectException(NotFoundExceptionInterface::class);
        $this->assertException($expected);
    }
}
