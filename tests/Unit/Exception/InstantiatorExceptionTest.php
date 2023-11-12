<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\Foobar;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(InstantiatorException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class InstantiatorExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testInstantiatorInstantiateThrowsInstantiatorException(): void
    {
        $this->assertConainerExceptionInterface(InstantiatorException::class);

        (new Instantiator())->instantiate(
            Container::getInstance(),
            Foobar::class,
            [null]
        );
    }
}
