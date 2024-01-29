<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(Instantiator::class)]
#[CoversClass(Container::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class InstantiatorTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testInstantiate(): void
    {
        self::assertInstanceOf(
            stdClass::class,
            $this->instantiator->instantiate(
                stdClass::class
            )
        );
    }
}
