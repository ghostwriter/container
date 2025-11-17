<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Definition\ContainerDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use stdClass;
use Tests\Fixture\Extension\StdClassOneExtension;
use Tests\Fixture\Factory\DoesNotExistFactory;
use Tests\Fixture\Factory\StdClassFactory;
use Tests\Unit\AbstractTestCase;
use Throwable;

/**
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress UndefinedClass
 */
#[CoversClass(ServiceNotFoundException::class)]
#[CoversClass(ContainerDefinition::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
final class ServiceNotFoundExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testContainerAlias(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container
            ->alias(stdClass::class, '')
        ;
    }

    /** @throws Throwable */
    public function testContainerAliasService(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->alias('', stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerAliasServiceEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->alias(' ', stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerAliasWithEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->alias(stdClass::class, ' ')
        ;
    }

    /** @throws Throwable */
    public function testContainerBindAbstract(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, '', stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerBindAbstractEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, ' ', stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerBindAbstractNotFound(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, 'does-not-exist', stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerBindConcrete(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->bind('', stdClass::class, stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerBindConcreteEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->bind(' ', stdClass::class, stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerBindConcreteNotFound(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->bind('does-not-exist', stdClass::class, stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerBindImplementation(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, stdClass::class, '');
    }

    /** @throws Throwable */
    public function testContainerBindImplementationNotFound(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, stdClass::class, 'does-not-exist');
    }

    /** @throws Throwable */
    public function testContainerBuildEmpty(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->build('');
    }

    /** @throws Throwable */
    public function testContainerBuildEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->build(' ');
    }

    /** @throws Throwable */
    public function testContainerBuildFactory(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->factory(stdClass::class, DoesNotExistFactory::class);

        $this->container->get(stdClass::class);
    }

    /** @throws Throwable */
    public function testContainerBuildNotFound(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->build('does-not-exist');
    }

    /** @throws Throwable */
    public function testContainerExtend(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->extend('', StdClassOneExtension::class);
    }

    /** @throws Throwable */
    public function testContainerExtendEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->extend(' ', StdClassOneExtension::class);
    }

    /** @throws Throwable */
    public function testContainerFactory(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->factory('', StdClassFactory::class);
    }

    /** @throws Throwable */
    public function testContainerFactoryEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->factory(' ', StdClassFactory::class);
    }

    /** @throws Throwable */
    public function testContainerGet(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->get('');
    }

    /** @throws Throwable */
    public function testContainerGetEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->get(' ');
    }

    /** @throws Throwable */
    public function testContainerGetNotFound(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->get('does-not-exist');
    }

    /** @throws Throwable */
    public function testContainerHas(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->has('');
    }

    /** @throws Throwable */
    public function testContainerHasEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->has(' ');
    }

    /** @throws Throwable */
    public function testContainerSetEmptySpace(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->set(' ', new stdClass());
    }

    /** @throws Throwable */
    public function testContainerSetEmptyString(): void
    {
        $this->assertException(ServiceNotFoundException::class);

        $this->container->set('', new stdClass());
    }
}
