<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(AliasNameAndServiceNameCannotBeTheSameException::class)]
#[CoversClass(Container::class)]
final class AliasNameAndServiceNameCannotBeTheSameExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        $this->assertException(AliasNameAndServiceNameCannotBeTheSameException::class);

        Container::getInstance()
                 ->alias(stdClass::class, stdClass::class);
    }
}
