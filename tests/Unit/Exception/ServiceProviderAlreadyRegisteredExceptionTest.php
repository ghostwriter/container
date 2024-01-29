<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Fixture\ServiceProvider\FoobarServiceProvider;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(ServiceProviderAlreadyRegisteredException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceProviderAlreadyRegisteredExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerProvide(): void
    {
        $this->assertException(ServiceProviderAlreadyRegisteredException::class);
        $this->expectExceptionMessage(FoobarServiceProvider::class);

        $this->container->provide(FoobarServiceProvider::class);
        $this->container->provide(FoobarServiceProvider::class);
    }
}
