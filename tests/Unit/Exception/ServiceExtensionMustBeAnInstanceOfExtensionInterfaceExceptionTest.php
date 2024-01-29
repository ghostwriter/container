<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceExtensionMustBeAnInstanceOfExtensionInterfaceExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerExtend(): void
    {
        $this->assertException(ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException::class);

        $this->container->extend(stdClass::class, stdClass::class);
    }
}
