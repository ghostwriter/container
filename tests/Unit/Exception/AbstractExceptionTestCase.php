<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
use InvalidArgumentException;
use Throwable;

abstract class AbstractExceptionTestCase extends AbstractTestCase
{
    /**
     * @param class-string<Throwable> $expected
     */
    public function assertConainerExceptionInterface(string $expected): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException($expected);
    }

    /**
     * @param class-string<Throwable> $expected
     */
    public function assertNotFoundExceptionInterface(string $expected): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->assertConainerExceptionInterface($expected);
    }
}
