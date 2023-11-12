<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(AliasNameAndServiceNameCannotBeTheSameException::class)]
#[CoversClass(Container::class)]
final class AliasNameAndServiceNameCannotBeTheSameExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        $this->assertConainerExceptionInterface(AliasNameAndServiceNameCannotBeTheSameException::class);

        Container::getInstance()
                 ->alias(stdClass::class, stdClass::class);
    }
}
