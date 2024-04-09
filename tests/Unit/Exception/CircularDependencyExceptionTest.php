<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Fixture\CircularDependency\ClassA;
use Ghostwriter\ContainerTests\Fixture\CircularDependency\ClassB;
use Ghostwriter\ContainerTests\Fixture\CircularDependency\ClassC;
use Ghostwriter\ContainerTests\Fixture\CircularDependency\ClassX;
use Ghostwriter\ContainerTests\Fixture\CircularDependency\ClassY;
use Ghostwriter\ContainerTests\Fixture\CircularDependency\ClassZ;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

use function implode;
use function sprintf;

#[CoversClass(CircularDependencyException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class CircularDependencyExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertException(CircularDependencyException::class);
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

        $this->container->build(ClassA::class);
    }
}
