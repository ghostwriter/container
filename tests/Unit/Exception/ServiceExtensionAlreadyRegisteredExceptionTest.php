<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceExtensionAlreadyRegisteredException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\Extension\StdClassOneExtension;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(ServiceExtensionAlreadyRegisteredException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceExtensionAlreadyRegisteredExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerExtend(): void
    {
        $this->assertException(ServiceExtensionAlreadyRegisteredException::class);

        $this->container->extend(stdClass::class, StdClassOneExtension::class);
        $this->container->extend(stdClass::class, StdClassOneExtension::class);
    }
}
