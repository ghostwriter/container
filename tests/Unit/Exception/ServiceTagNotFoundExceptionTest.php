<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;
use function iterator_to_array;

#[CoversClass(ServiceTagNotFoundException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceTagNotFoundExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerTagged(): void
    {
        $this->assertNotFoundException(ServiceTagNotFoundException::class);

        iterator_to_array($this->container->tagged('tag-not-found'));
    }

    /**
     * @throws Throwable
     */
    public function testContainerUntag(): void
    {
        $this->assertNotFoundException(ServiceTagNotFoundException::class);

        $this->container->untag(self::class, ['tag-not-found']);
    }
}
