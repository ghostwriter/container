<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ReflectorException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(ReflectorException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ReflectorExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertConainerExceptionInterface(ReflectorException::class);
        $this->expectExceptionMessage('Class "does-not-exist" does not exist');

        $container = Container::getInstance();

        $container->build('does-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testReflectorReflectClass(): void
    {
        $this->assertConainerExceptionInterface(ReflectorException::class);
        $this->expectExceptionMessage('Class "dose-not-exist" does not exist');

        (new Reflector())->reflectClass('dose-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testReflectorReflectFunction(): void
    {
        $this->assertConainerExceptionInterface(ReflectorException::class);
        $this->expectExceptionMessage('Function dose-not-exist() does not exist');

        (new Reflector())->reflectFunction('dose-not-exist');
    }
}
