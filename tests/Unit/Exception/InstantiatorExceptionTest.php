<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Definition\ContainerDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Fixture\FoobarWithoutFactoryAttribute;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(InstantiatorException::class)]
#[CoversClass(ContainerDefinition::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
final class InstantiatorExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testInstantiatorInstantiateThrowsInstantiatorException(): void
    {
        $this->assertException(InstantiatorException::class);

        $this->container->build(FoobarWithoutFactoryAttribute::class, [null]);
    }

    /** @throws Throwable */
    public function testInstantiatorInstantiateWithNamedParameterThrowsInstantiatorException(): void
    {
        $this->assertException(InstantiatorException::class);

        $this->container->build(FoobarWithoutFactoryAttribute::class, [
            'count' => null,
        ]);
    }
}
