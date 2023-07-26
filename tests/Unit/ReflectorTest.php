<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Ghostwriter\Container\ExceptionInterface;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\ReflectorException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;

#[CoversClass(Reflector::class)]
#[UsesClass(ReflectorException::class)]
final class ReflectorTest extends TestCase
{
    private Reflector $reflector;

    protected function setup(): void
    {
        $this->reflector = new Reflector();
    }

    public function testGetReflectionClass(): void
    {
        self::assertInstanceOf(ReflectionClass::class, $this->reflector->getReflectionClass(self::class));
    }

    public function testGetReflectionClassEx(): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->expectException(ReflectorException::class);
        $this->expectExceptionMessage('Class "dose-not-exist" does not exist');

        self::assertInstanceOf(ReflectionClass::class, $this->reflector->getReflectionClass('dose-not-exist'));
    }

    public function testGetReflectionFunction(): void
    {
        self::assertInstanceOf(ReflectionFunction::class, $this->reflector->getReflectionFunction(static fn () => null));
    }

    public function testGetReflectionFunctionEx(): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->expectException(ReflectorException::class);
        $this->expectExceptionMessage('Function dose-not-exist() does not exist');

        self::assertInstanceOf(ReflectionFunction::class, $this->reflector->getReflectionFunction('dose-not-exist'));
    }
}