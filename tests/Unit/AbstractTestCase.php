<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class AbstractTestCase extends TestCase
{
    protected Container $container;

    final protected function setUp(): void
    {
        parent::setUp();

        $this->container = Container::getInstance();
    }

    /**
     * @throws Throwable
     */
    final protected function tearDown(): void
    {
        parent::tearDown();

        $this->container->purge();
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
