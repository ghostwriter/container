<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ReflectorException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(ReflectorException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ReflectorExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertException(ReflectorException::class);
        $this->expectExceptionMessage('Class "does-not-exist" does not exist');

        $this->container->build('does-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testReflectorReflectClass(): void
    {
        $this->assertException(ReflectorException::class);
        $this->expectExceptionMessage('Class "dose-not-exist" does not exist');

        $this->reflector->reflectClass('dose-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testReflectorReflectFunction(): void
    {
        $this->assertException(ReflectorException::class);
        $this->expectExceptionMessage('Function dose-not-exist() does not exist');

        $this->reflector->reflectFunction('dose-not-exist');
    }
}
