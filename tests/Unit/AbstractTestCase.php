<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class AbstractTestCase extends TestCase
{
    protected Container $container;

    protected Instantiator $instantiator;

    protected ParameterBuilder $parameterBuilder;

    protected Reflector $reflector;

    final protected function setUp(): void
    {
        parent::setUp();

        $this->container = Container::getInstance();

        $this->reflector = new Reflector();

        $this->parameterBuilder = new ParameterBuilder($this->container);

        $this->instantiator = Instantiator::new($this->reflector, $this->parameterBuilder);
    }

    /**
     * @throws Throwable
     */
    final protected function tearDown(): void
    {
        parent::tearDown();

        $this->container->__destruct();
    }

    /**
     * @param class-string<Throwable> $expected
     */
    final public function assertException(string $expected): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException($expected);
    }

    /**
     * @param class-string<Throwable> $expected
     */
    final public function assertNotFoundException(string $expected): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->assertException($expected);
    }
}
