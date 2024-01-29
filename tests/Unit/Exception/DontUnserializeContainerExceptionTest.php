<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;
use function mb_strlen;
use function sprintf;
use function unserialize;

#[CoversClass(DontUnserializeContainerException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class DontUnserializeContainerExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testUnserialize(): void
    {
        $this->assertException(DontUnserializeContainerException::class);

        unserialize(
            // mocks a serialized Container::class
            sprintf('O:%s:"%s":0:{}', mb_strlen(Container::class), Container::class)
        );
    }
}
