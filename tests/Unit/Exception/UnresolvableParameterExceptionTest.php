<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\UnresolvableParameter;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionParameter;
use Throwable;

#[CoversClass(UnresolvableParameterException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class UnresolvableParameterExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertConainerExceptionInterface(UnresolvableParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable class parameter "$number" in "%s::%s"; does not have a default value.',
            UnresolvableParameter::class,
            '__construct()'
        ));

        Container::getInstance()->build(UnresolvableParameter::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerCall(): void
    {
        $this->assertConainerExceptionInterface(UnresolvableParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable function parameter "%s" in "%s"; does not have a default value.',
            '$event',
            'Ghostwriter\Container\Tests\Fixture\typelessFunction()',
        ));

        Container::getInstance()->call('Ghostwriter\Container\Tests\Fixture\typelessFunction');
    }

    /**
     * @throws Throwable
     */
    public function testParameterBuilderBuild(): void
    {
        $this->assertConainerExceptionInterface(UnresolvableParameterException::class);

        (new ParameterBuilder())->build(
            Container::getInstance(),
            [
                new ReflectionParameter(
                    static fn($foo) => $foo,
                    'foo'
                ),
            ]
        );
    }
}
