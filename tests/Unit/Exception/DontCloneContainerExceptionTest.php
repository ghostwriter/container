<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\DontCloneContainerException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(DontCloneContainerException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class DontCloneContainerExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testClone(): void
    {
        $this->assertConainerExceptionInterface(DontCloneContainerException::class);

        $container = Container::getInstance();

        self::assertNull(clone $container);
    }
}