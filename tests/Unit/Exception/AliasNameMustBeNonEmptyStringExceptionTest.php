<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;
use Ghostwriter\Container\Instantiator;

#[CoversClass(AliasNameMustBeNonEmptyStringException::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(Container::class)]
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
