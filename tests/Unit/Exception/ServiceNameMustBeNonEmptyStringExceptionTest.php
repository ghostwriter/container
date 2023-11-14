<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\Extension\StdClassOneExtension;
use Ghostwriter\Container\Tests\Fixture\StdClassFactory;
use Ghostwriter\Container\Tests\Unit\AbstractTestCase;
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
    public function testContainerExtend(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->extend('', StdClassOneExtension::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerExtendEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->extend(' ', StdClassOneExtension::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerGet(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->get('');
    }

    /**
     * @throws Throwable
     */
    public function testContainerGetEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->get(' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerHas(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->has('');
    }

    /**
     * @throws Throwable
     */
    public function testContainerHasEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->has(' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindAbstract(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->bind(stdClass::class, '', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindAbstractEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->bind(stdClass::class, ' ', stdClass::class);
    }


    /**
     * @throws Throwable
     */
    public function testContainerBindConcrete(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->bind('', stdClass::class, stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindConcreteEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->bind(' ', stdClass::class, stdClass::class);
    }


    /**
     * @throws Throwable
     */
    public function testContainerBindImplementation(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->bind(stdClass::class, stdClass::class, '');
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindImplementationEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->bind(stdClass::class, stdClass::class, ' ');
    }


    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->build('');
    }

    /**
     * @throws Throwable
     */
    public function testContainerBuildEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->build(' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterAbstract(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->register('', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterAbstractEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->register(' ', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterConcrete(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->register(stdClass::class, '');
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterConcreteEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->register(stdClass::class, ' ');
    }

    /**
     * @throws Throwable
     */
    public function testContainerAliasService(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->alias(stdClass::class, '');
    }

    /**
     * @throws Throwable
     */
    public function testContainerAliasServiceEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->alias(stdClass::class, ' ');
    }


    /**
     * @throws Throwable
     */
    public function testContainerSet(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->set('', new stdClass());
    }

    /**
     * @throws Throwable
     */
    public function testContainerSetEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->set(' ', new stdClass());
    }


    /**
     * @throws Throwable
     */
    public function testContainerTag(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->tag('', [stdClass::class]);
    }

    /**
     * @throws Throwable
     */
    public function testContainerTagEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->tag(' ', [stdClass::class]);
    }


    /**
     * @throws Throwable
     */
    public function testContainerFactory(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->factory('', StdClassFactory::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerFactoryEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        Container::getInstance()->factory(' ', StdClassFactory::class);
    }

}
