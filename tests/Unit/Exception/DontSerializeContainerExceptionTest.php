<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(DontSerializeContainerException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class DontSerializeContainerExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testSerialize(): void
    {
        $this->assertConainerExceptionInterface(DontSerializeContainerException::class);

        self::assertNull(serialize(Container::getInstance()));
    }
}
