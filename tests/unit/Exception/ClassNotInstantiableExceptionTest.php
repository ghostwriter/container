<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
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
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(ClassNotInstantiableException::class)]
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
final class ClassNotInstantiableExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertException(ClassNotInstantiableException::class);
        $this->expectExceptionMessage(Throwable::class);

        $this->container->build(Throwable::class);
    }

    /**
     * @throws Throwable
     */
    public function testInstantiatorInstantiate(): void
    {
        $this->assertException(ClassNotInstantiableException::class);

        $this->container->build(AbstractTestCase::class);
    }
}
