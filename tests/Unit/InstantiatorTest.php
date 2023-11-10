<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\InstantiatorException;
use Ghostwriter\Container\Exception\ReflectorException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\Foobar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use stdClass;

#[CoversClass(Instantiator::class)]
#[UsesClass(Container::class)]
#[UsesClass(ParameterBuilder::class)]
#[UsesClass(Reflector::class)]
final class InstantiatorTest extends AbstractTestCase
{
    private Instantiator $instantiator;

    protected function setUp(): void
    {
        $this->instantiator = new Instantiator();
    }

    public function testBuildParameters(): void
    {
        $container = Container::getInstance();

        self::assertSame(
            [],
            $this->instantiator->buildParameters(
                $container,
                static fn () => new stdClass()
            )
        );
    }
    public function testInstantiate(): void
    {
        $container = Container::getInstance();

        self::assertInstanceOf(
            stdClass::class,
            $this->instantiator->instantiate(
                $container,
                stdClass::class
            )
        );
    }

    public function testInstantiateThrowsClassNotInstantiableException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ClassNotInstantiableException::class);

        $container = Container::getInstance();

        $this->instantiator->instantiate($container, AbstractTestCase::class);
    }
    public function testInstantiateThrowsInstantiatorException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InstantiatorException::class);

        $container = Container::getInstance();

        $this->instantiator->instantiate($container, Foobar::class, [null]);
    }
}
