<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionParameter;
use stdClass;
use Throwable;

#[CoversClass(Container::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(Reflector::class)]
final class ParameterBuilderTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public static function parameterBuilderBuildDataProvider(): Generator
    {
        $stdClass = new stdClass();

        $closure = static fn(stdClass $foo): object => $stdClass;

        $empty = [];

        yield from [
            'no parameters & no arguments' => [$empty, $empty, $empty],

            'no parameters & arguments' => [$empty, ['foo' => $stdClass], [$stdClass]],

            'parameters & no arguments' => [
                [new ReflectionParameter($closure, 'foo')],
                $empty,
                [$stdClass]
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('parameterBuilderBuildDataProvider')]
    public function testBuild(
        array $parameters,
        array $arguments,
        array $expected
    ): void
    {
        self::assertEquals(
            $expected,
            (new ParameterBuilder())->build(
                Container::getInstance(),
                $parameters,
                $arguments
            )
        );
    }
}
