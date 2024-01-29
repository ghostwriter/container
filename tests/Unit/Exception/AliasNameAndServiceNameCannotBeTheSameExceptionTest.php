<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(AliasNameAndServiceNameCannotBeTheSameException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class AliasNameAndServiceNameCannotBeTheSameExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        $this->assertException(AliasNameAndServiceNameCannotBeTheSameException::class);

        $this->container
            ->alias(stdClass::class, stdClass::class);
    }
}
