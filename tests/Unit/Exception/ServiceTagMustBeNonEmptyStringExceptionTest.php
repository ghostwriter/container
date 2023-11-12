<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

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

        Container::getInstance()->tag('service', ['']);
    }

    /**
     * @throws Throwable
     */
    public function testContainerTagWithEmptySpace(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        Container::getInstance()->tag('service', [' ']);
    }

    /**
     * @throws Throwable
     */
    public function testContainerTagged(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        iterator_to_array(Container::getInstance()->tagged(''));
    }

    /**
     * @throws Throwable
     */
    public function testContainerTaggedWithEmptySpace(): void
    {
        $this->assertException(ServiceTagMustBeNonEmptyStringException::class);

        iterator_to_array(Container::getInstance()->tagged(' '));
    }
}
