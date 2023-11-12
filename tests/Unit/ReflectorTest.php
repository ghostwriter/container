<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

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
        self::assertSame(
            self::class,
            (new Reflector())
                ->reflectClass(self::class)
                ->getName()
        );
    }

    /**
     * @throws Throwable
     */
    public function testReflectFunction(): void
    {
        self::assertTrue(
            (new Reflector())
                ->reflectFunction(static fn(): null => null)
                ->isStatic()
        );
    }
}
