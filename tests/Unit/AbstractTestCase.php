<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class AbstractTestCase extends TestCase
{
    protected Container $container;

    #[Override]
    final protected function setUp(): void
    {
        parent::setUp();

        $this->container = Container::getInstance();
    }

    /**
     * @throws Throwable
     */
    #[Override]
    final protected function tearDown(): void
    {
        parent::tearDown();

        $this->container->clear();
    }

    /**
     * @param class-string<Throwable> $expected
     */
    final public function assertException(string $expected): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException($expected);
    }

    /**
     * @param class-string<Throwable> $expected
     */
    final public function assertNotFoundException(string $expected): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->assertException($expected);
    }
}
