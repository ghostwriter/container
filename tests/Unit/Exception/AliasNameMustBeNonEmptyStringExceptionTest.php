<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(AliasNameMustBeNonEmptyStringException::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(Container::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class AliasNameMustBeNonEmptyStringExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        $this->assertException(AliasNameMustBeNonEmptyStringException::class);

        $this->container
            ->alias('', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerAliasWithEmptySpace(): void
    {
        $this->assertException(AliasNameMustBeNonEmptyStringException::class);

        $this->container
            ->alias(' ', stdClass::class);
    }
}
