<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Fixture\UnresolvableParameter;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionParameter;
use Throwable;

use function sprintf;

#[CoversClass(UnresolvableParameterException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class UnresolvableParameterExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertException(UnresolvableParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable class parameter "$number" in "%s::%s"; does not have a default value.',
            UnresolvableParameter::class,
            '__construct()'
        ));

        $this->container->build(UnresolvableParameter::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerCall(): void
    {
        $this->assertException(UnresolvableParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable function parameter "%s" in "%s"; does not have a default value.',
            '$event',
            'Ghostwriter\ContainerTests\Fixture\typelessFunction()',
        ));

        $this->container->call('Ghostwriter\ContainerTests\Fixture\typelessFunction');
    }

    /**
     * @throws Throwable
     */
    public function testParameterBuilderBuild(): void
    {
        $this->assertException(UnresolvableParameterException::class);

        $this->parameterBuilder->build([
            new ReflectionParameter(
                static fn ($foo) => $foo,
                'foo'
            ),
        ]);
    }
}
