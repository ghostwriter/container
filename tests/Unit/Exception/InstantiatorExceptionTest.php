<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Fixture\Foobar;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(InstantiatorException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class InstantiatorExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testInstantiatorInstantiateThrowsInstantiatorException(): void
    {
        $this->assertException(InstantiatorException::class);

        $this->instantiator->instantiate(Foobar::class, [null]);
    }
}
