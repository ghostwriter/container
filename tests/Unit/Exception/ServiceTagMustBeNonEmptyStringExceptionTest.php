<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;
use function iterator_to_array;

#[CoversClass(ServiceTagMustBeNonEmptyStringException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceTagMustBeNonEmptyStringExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerTag(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        $this->container->tag('service', ['']);
    }

    /**
     * @throws Throwable
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

    /**
     * @throws Throwable
     */
    public function testContainerTagWithEmptySpace(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        $this->container->tag('service', [' ']);
    }
}
