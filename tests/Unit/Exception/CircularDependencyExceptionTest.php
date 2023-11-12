<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassA;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassB;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassC;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassX;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassY;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassZ;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(CircularDependencyException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class CircularDependencyExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertConainerExceptionInterface(CircularDependencyException::class);
        $this->expectExceptionMessage(sprintf(
            'Class: %s',
            implode(
                ' -> ',
                [
                    ClassA::class,
                    ClassB::class,
                    ClassC::class,
                    ClassX::class,
                    ClassY::class,
                    ClassZ::class,
                    ClassA::class,
                ]
            )
        ));

        Container::getInstance()->build(ClassA::class);
    }
}
