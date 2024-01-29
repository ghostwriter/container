<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Fixture\InvalidStdClassFactory;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceFactoryMustBeAnInstanceOfFactoryInterfaceExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerFactory(): void
    {
        $this->assertException(ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException::class);

        $this->container->factory(stdClass::class, InvalidStdClassFactory::class);
    }
}
