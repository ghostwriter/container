<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\InvalidArgumentException;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use stdClass;
use Tests\Fixture\InvalidStdClassFactoryThatDoesNotImplementFactoryInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(InvalidArgumentException::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
final class InvalidArgumentExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     *
     *
     */
    public function testContainerFactory(): void
    {
        $this->assertException(InvalidArgumentException::class);

        $this->container->factory(stdClass::class, stdClass::class);
    }

    /**
     * @throws Throwable
     *
     *
     */
    public function testContainerFactoryWithInvalidFactory(): void
    {
        $this->assertException(InvalidArgumentException::class);

        $this->container->factory(stdClass::class, InvalidStdClassFactoryThatDoesNotImplementFactoryInterface::class);
    }
    /**
     * @throws Throwable
     */
    public function testContainerDefine(): void
    {
        $this->assertException(InvalidArgumentException::class);
        $this->expectExceptionMessage(self::class);

        $this->container->define(self::class);
    }


    /**
     * @throws Throwable
     *
     *
     */
    public function testContainerExtend(): void
    {
        self::expectExceptionMessage(
            sprintf(
                'Service extension "%s" for service "%s" must implement %s.',
                stdClass::class,
                stdClass::class,
                ExtensionInterface::class
            )
        );

        $this->assertException(InvalidArgumentException::class);

        $this->container->extend(stdClass::class, stdClass::class);
    }
}
