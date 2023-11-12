<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(AliasNameMustBeNonEmptyStringException::class)]
#[CoversClass(Container::class)]
final class AliasNameMustBeNonEmptyStringExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        $this->assertConainerExceptionInterface(AliasNameMustBeNonEmptyStringException::class);

        Container::getInstance()
                 ->alias('', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerAliasWithEmptySpace(): void
    {
        $this->assertConainerExceptionInterface(AliasNameMustBeNonEmptyStringException::class);

        Container::getInstance()
                 ->alias(' ', stdClass::class);
    }
}
