<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\ServiceProvider\FoobarServiceProvider;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
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

        Container::getInstance()->provide(FoobarServiceProvider::class);
        Container::getInstance()->provide(FoobarServiceProvider::class);
    }
}
