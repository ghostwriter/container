<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\ReflectorException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Reflector::class)]
#[UsesClass(ReflectorException::class)]
final class ReflectorTest extends TestCase
{
    public function testGetReflectionClass(): void
    {
        self::assertInstanceOf(ReflectionClass::class, Reflector::getReflectionClass(self::class));
    }

    public function testGetReflectionClassEx(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ReflectorException::class);
        $this->expectExceptionMessage('Class "dose-not-exist" does not exist');

        self::assertInstanceOf(ReflectionClass::class, Reflector::getReflectionClass('dose-not-exist'));
    }
}
