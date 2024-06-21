<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException::class)]
#[CoversClass(Aliases::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Container::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extension::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Factory::class)]
#[CoversClass(Inject::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Tags::class)]
final class ServiceExtensionMustBeAnInstanceOfExtensionInterfaceExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     *
     * @psalm-suppress InvalidArgument
     */
    public function testContainerExtend(): void
    {
        $this->assertException(ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException::class);

        $this->container->extend(stdClass::class, stdClass::class);
    }
}
