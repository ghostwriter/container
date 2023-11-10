<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use ReflectionParameter;
use stdClass;
use Throwable;

#[CoversClass(Container::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(UnresolvableParameterException::class)]
#[UsesClass(Instantiator::class)]
#[UsesClass(Reflector::class)]
final class ParameterBuilderTest extends AbstractTestCase
{
    private ParameterBuilder $parameterBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parameterBuilder = new ParameterBuilder();
    }

    /**
     * @throws Throwable
     */
    public static function parameterBuilderBuildDataProvider(): Generator
    {
        $container = Container::getInstance();

        $stdClass = $container->get(stdClass::class);

        $closure = static fn (stdClass $foo): object => $stdClass;

        $empty = [];

        $withoutParameters = $empty;

        $withParameters = [
            new ReflectionParameter(
                $closure,
                'foo'
            ),
        ];

        $withoutArguments = $empty;
        $withArguments = [
            'foo' => $stdClass,
        ];

        yield from [
            'no parameters & no arguments' => [
                $container,
                $withoutParameters,
                $withoutArguments,
                $empty,
            ],

            'no parameters & arguments' => [
                $container,
                $withoutParameters,
                $withArguments,
                [$stdClass],
            ],

            'parameters & no arguments' => [
                $container,
                $withParameters,
                $withoutArguments,
                [$stdClass],
            ],
        ];
    }

    public function testParameterBuilder(): void
    {
        self::assertInstanceOf(
            ParameterBuilder::class,
            $this->parameterBuilder
        );
    }

    #[DataProvider('parameterBuilderBuildDataProvider')]
    public function testParameterBuilderBuild(
        ContainerInterface $container,
        array $parameters,
        array $arguments,
        array $expected
    ): void {
        self::assertEquals(
            $expected,
            $this->parameterBuilder->build(
                $container,
                $parameters,
                $arguments
            )
        );
    }

    public function testParameterBuilderBuildThrowsUnresolvableParameterException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(UnresolvableParameterException::class);

        $this->parameterBuilder->build(
            Container::getInstance(),
            [
                new ReflectionParameter(
                    static fn ($foo) => $foo,
                    'foo'
                ),
            ]
        );
    }
}
