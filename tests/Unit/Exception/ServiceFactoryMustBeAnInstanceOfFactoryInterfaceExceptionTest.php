<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceFactoryMustBeAnInstanceOfFactoryInterfaceException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\InvalidStdClassFactory;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
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
