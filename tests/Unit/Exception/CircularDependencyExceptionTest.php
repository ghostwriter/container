<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Name\Alias;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Fixture\CircularDependency\ClassA;
use Tests\Fixture\CircularDependency\ClassB;
use Tests\Fixture\CircularDependency\ClassC;
use Tests\Fixture\CircularDependency\ClassX;
use Tests\Fixture\CircularDependency\ClassY;
use Tests\Fixture\CircularDependency\ClassZ;
use Tests\Unit\AbstractTestCase;
use Throwable;
use function implode;
use function sprintf;

#[CoversClass(CircularDependencyException::class)]

#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
final class CircularDependencyExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
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
