<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use Ghostwriter\Container\Name\Alias;
use Ghostwriter\Container\Name\Factory as FactoryName;
use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Tests\Fixture\Extension\StdClassOneExtension;
use Tests\Fixture\Factory\StdClassFactory;
use Tests\Unit\AbstractTestCase;
use Throwable;

/**
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress InvalidArgument
 * @psalm-suppress UndefinedClass
 */
#[CoversClass(ServiceNameMustBeNonEmptyStringException::class)]
#[CoversClass(Aliases::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Container::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extension::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Factory::class)]
#[CoversClass(Inject::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Service::class)]
#[CoversClass(Alias::class)]
#[CoversClass(Tags::class)]
#[CoversClass(FactoryName::class)]
final class ServiceNameMustBeNonEmptyStringExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerAliasService(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->alias('', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerAliasServiceEmptySpace(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->alias(' ', stdClass::class);
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

    //    /**
    //     * @throws Throwable
    //     */
    //    public function testContainerRegisterAbstract(): void
    //    {
    //        $this->assertException(AliasNameMustBeNonEmptyStringException::class);
    //
    //        $this->container->register('', stdClass::class);
    //    }
    //
    //    /**
    //     * @throws Throwable
    //     */
    //    public function testContainerRegisterAbstractEmptySpace(): void
    //    {
    //        $this->assertException(AliasNameMustBeNonEmptyStringException::class);
    //
    //        $this->container->register(' ', stdClass::class);
    //    }
    //
    //    /**
    //     * @throws Throwable
    //     */
    //    public function testContainerRegisterConcrete(): void
    //    {
    //        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);
    //
    //        $this->container->register(stdClass::class, '');
    //    }
    //
    //    /**
    //     * @throws Throwable
    //     */
    //    public function testContainerRegisterConcreteEmptySpace(): void
    //    {
    //        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);
    //
    //        $this->container->register(stdClass::class, ' ');
    //    }

    /**
     * @throws Throwable
     *
     */
    public function testContainerSet(): void
    {
        $this->assertException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->set('', new stdClass());
    }

    /**
     * @throws Throwable
     *
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
