<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(ClassNotInstantiableException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ClassNotInstantiableExceptionTest extends AbstractExceptionTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertConainerExceptionInterface(ClassNotInstantiableException::class);
        $this->expectExceptionMessage(Throwable::class);

        Container::getInstance()->build(Throwable::class);
    }

    /**
     * @throws Throwable
     */
    public function testInstantiatorInstantiate(): void
    {
        $this->assertConainerExceptionInterface(ClassNotInstantiableException::class);

        (new Instantiator())->instantiate(
            Container::getInstance(),
            AbstractExceptionTestCase::class
        );
    }
}
