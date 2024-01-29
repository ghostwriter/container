<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\ContainerTests\Fixture\Extension\StdClassOneExtension;
use Ghostwriter\ContainerTests\Fixture\StdClassFactory;
use Ghostwriter\ContainerTests\Unit\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Throwable;

#[CoversClass(ServiceNameMustBeNonEmptyStringException::class)]
#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ServiceNameMustBeNonEmptyStringExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerAliasService(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->alias(stdClass::class, '');
    }

    /**
     * @throws Throwable
     */
    public function testContainerAliasServiceEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->alias(stdClass::class, ' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindAbstract(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(stdClass::class, '', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindAbstractEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(stdClass::class, ' ', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindConcrete(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind('', stdClass::class, stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindConcreteEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(' ', stdClass::class, stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindImplementation(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(stdClass::class, stdClass::class, '');
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindImplementationEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(stdClass::class, stdClass::class, ' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->build('');
    }

    /**
     * @throws Throwable
     */
    public function testContainerBuildEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->build(' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerExtend(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->extend('', StdClassOneExtension::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerExtendEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->extend(' ', StdClassOneExtension::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerFactory(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->factory('', StdClassFactory::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerFactoryEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->factory(' ', StdClassFactory::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerGet(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->get('');
    }

    /**
     * @throws Throwable
     */
    public function testContainerGetEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->get(' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerHas(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->has('');
    }

    /**
     * @throws Throwable
     */
    public function testContainerHasEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->has(' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterAbstract(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->register('', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterAbstractEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->register(' ', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterConcrete(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->register(stdClass::class, '');
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterConcreteEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->register(stdClass::class, ' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerSet(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->set('', new stdClass());
    }

    /**
     * @throws Throwable
     */
    public function testContainerSetEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->set(' ', new stdClass());
    }

    /**
     * @throws Throwable
     */
    public function testContainerTag(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->tag('', [stdClass::class]);
    }

    /**
     * @throws Throwable
     */
    public function testContainerTagEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->tag(' ', [stdClass::class]);
    }
}
