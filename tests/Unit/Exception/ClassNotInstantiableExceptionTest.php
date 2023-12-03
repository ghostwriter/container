<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(ClassNotInstantiableException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ClassNotInstantiableExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertException(ClassNotInstantiableException::class);
        $this->expectExceptionMessage(Throwable::class);

        $this->container->build(Throwable::class);
    }

    /**
     * @throws Throwable
     */
    public function testInstantiatorInstantiate(): void
    {
        $this->assertException(ClassNotInstantiableException::class);

        $this->instantiator->instantiate(
            $this->container,
            AbstractTestCase::class
        );
    }
}
