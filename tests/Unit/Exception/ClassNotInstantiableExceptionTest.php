<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Name\Alias;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ClassNotInstantiableException::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]

final class ClassNotInstantiableExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testContainerBuild(): void
    {
        $this->assertException(ClassNotInstantiableException::class);
        $this->expectExceptionMessage(Throwable::class);

        $this->container->build(Throwable::class);
    }

    /** @throws Throwable */
    public function testInstantiatorInstantiate(): void
    {
        $this->assertException(ClassNotInstantiableException::class);

        $this->container->build(AbstractTestCase::class);
    }
}
