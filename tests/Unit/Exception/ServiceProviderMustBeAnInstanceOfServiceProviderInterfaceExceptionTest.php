<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerProvide(): void
    {
        $this->assertException(ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException::class);
        $this->expectExceptionMessage(ServiceProviderInterface::class);

        $this->container->provide(ServiceProviderInterface::class);
    }
}
