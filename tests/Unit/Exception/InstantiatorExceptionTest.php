<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use Ghostwriter\Container\Name\Alias;
use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Fixture\FoobarWithoutFactoryAttribute;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(InstantiatorException::class)]
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
#[CoversClass(Service::class)]
#[CoversClass(Alias::class)]
final class InstantiatorExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testInstantiatorInstantiateThrowsInstantiatorException(): void
    {
        $this->assertException(InstantiatorException::class);

        $this->container->build(FoobarWithoutFactoryAttribute::class, [null]);
    }

    /**
     * @throws Throwable
     */
    public function testInstantiatorInstantiateWithNamedParameterThrowsInstantiatorException(): void
    {
        $this->assertException(InstantiatorException::class);

        $this->container->build(FoobarWithoutFactoryAttribute::class, [
            'count' => null,
        ]);
    }
}
