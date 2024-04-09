<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

/**
 * @see Reflector
 */
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ReflectorTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testReflectClass(): void
    {
        self::assertSame(self::class, $this->reflector ->reflectClass(self::class) ->getName());
    }

    /**
     * @throws Throwable
     */
    public function testReflectClassNotSame(): void
    {
        self::assertNotSame(
            $this->reflector->reflectClass(self::class),
            $this->reflector->reflectClass(self::class)
        );
    }

    /**
     * @throws Throwable
     */
    public function testReflectFunction(): void
    {
        self::assertTrue($this->reflector ->reflectFunction(static fn (): null => null) ->isStatic());
    }

    /**
     * @throws Throwable
     */
    public function testReflectFunctionNotSame(): void
    {
        $closure = static fn (): null => null;

        self::assertNotSame(
            $this->reflector->reflectFunction($closure),
            $this->reflector->reflectFunction($closure)
        );
    }
}
