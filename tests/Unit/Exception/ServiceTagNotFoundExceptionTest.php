<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

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
