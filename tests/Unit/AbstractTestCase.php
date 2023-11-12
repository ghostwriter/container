<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @throws Throwable
     */
    final protected function tearDown(): void
    {
        parent::tearDown();

        Container::getInstance()->__destruct();
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
