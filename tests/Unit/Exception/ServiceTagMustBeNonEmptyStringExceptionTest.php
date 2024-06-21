<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
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
use Tests\Unit\AbstractTestCase;
use Throwable;

use function iterator_to_array;

#[CoversClass(ServiceTagMustBeNonEmptyStringException::class)]
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
final class ServiceTagMustBeNonEmptyStringExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     *
     * @psalm-suppress InvalidArgument
     */
    public function testContainerTag(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        $this->container->tag(self::class, ['']);
    }

    /**
     * @throws Throwable
     */
    public function testContainerTagWithEmptySpace(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        $this->container->tag(self::class, [' ']);
    }

    /**
     * @throws Throwable
     *
     * @psalm-suppress InvalidArgument
     */
    public function testContainerTagged(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        iterator_to_array($this->container->tagged(''));
    }

    /**
     * @throws Throwable
     */
    public function testContainerTaggedWithEmptySpace(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        iterator_to_array($this->container->tagged(' '));
    }
}
