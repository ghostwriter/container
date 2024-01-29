<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;
use function serialize;

#[CoversClass(DontSerializeContainerException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class DontSerializeContainerExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testSerialize(): void
    {
        $this->assertException(DontSerializeContainerException::class);

        self::assertNull(serialize($this->container));
    }
}
