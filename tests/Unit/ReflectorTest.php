<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Ghostwriter\Container\Exception\ReflectorException;
use Ghostwriter\Container\Interface\ExceptionInterface;
use Ghostwriter\Container\Reflector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;

#[CoversClass(Reflector::class)]
#[UsesClass(ReflectorException::class)]
final class ReflectorTest extends AbstractTestCase
{
    private Reflector $reflector;

    protected function setup(): void
    {
        $this->reflector = new Reflector();
    }

    public function testReflectClass(): void
    {
        self::assertSame(
            self::class,
            $this->reflector->reflectClass(self::class)->getName()
        );
    }

    public function testReflectClassThrowsReflectorException(): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->expectException(ReflectorException::class);
        $this->expectExceptionMessage('Class "dose-not-exist" does not exist');

        $this->reflector->reflectClass('dose-not-exist');
    }

    public function testReflectFunction(): void
    {
        self::assertTrue(
            $this->reflector->reflectFunction(static fn () => null)->isStatic()
        );
    }

    public function testReflectFunctionThrowsReflectorException(): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->expectException(ReflectorException::class);
        $this->expectExceptionMessage('Function dose-not-exist() does not exist');

        $this->reflector->reflectFunction('dose-not-exist');
    }
}
