<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\Foobar;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
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

        $this->instantiator->instantiate(
            $this->container,
            Foobar::class,
            [null]
        );
    }
}
