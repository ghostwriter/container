<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\CircularDependency\ClassA;
use Tests\Fixture\CircularDependency\ClassB;
use Tests\Fixture\CircularDependency\ClassC;
use Tests\Fixture\CircularDependency\ClassX;
use Tests\Fixture\CircularDependency\ClassY;
use Tests\Fixture\CircularDependency\ClassZ;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(CircularDependencyException::class)]
#[CoversClass(Aliases::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Container::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extension::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Factory::class)]
#[CoversClass(Inject::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Tags::class)]
final class CircularDependencyExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertException(CircularDependencyException::class);
        $this->expectExceptionMessage(\sprintf(
            'Class: %s',
            \implode(
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
