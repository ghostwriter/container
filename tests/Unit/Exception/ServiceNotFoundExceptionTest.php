<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(ServiceNotFoundException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceNotFoundExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBindAbstract(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        Container::getInstance()->bind(stdClass::class, 'does-not-exist', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindConcrete(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        Container::getInstance()->bind('does-not-exist', stdClass::class, stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindImplementation(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        Container::getInstance()->bind(stdClass::class, stdClass::class, 'does-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testContainerGet(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        Container::getInstance()->get('does-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testContainerUntag(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        Container::getInstance()->tag(self::class, [self::class]);
        Container::getInstance()->untag(ServiceNotFoundException::class, [self::class]);
    }
}
