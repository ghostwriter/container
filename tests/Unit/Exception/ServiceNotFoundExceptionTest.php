<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
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
use Ghostwriter\Container\Name\Service;
use Ghostwriter\Container\Name\Tag;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Tests\Unit\AbstractTestCase;
use Throwable;

/**
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress UndefinedClass
 */
#[CoversClass(ServiceNotFoundException::class)]
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
#[CoversClass(Tags::class)]
#[CoversClass(Service::class)]
#[CoversClass(Tag::class)]
#[CoversClass(Alias::class)]
final class ServiceNotFoundExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerBindAbstract(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, 'does-not-exist', stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindConcrete(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->bind('does-not-exist', stdClass::class, stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerBindImplementation(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, stdClass::class, 'does-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testContainerBuild(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->define(stdClass::class, fn (): object => $this->container->build('does-not-exist'));

        $this->container->get(stdClass::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerGet(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->get('does-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testContainerUntag(): void
    {
        $this->assertNotFoundException(ServiceNotFoundException::class);

        $this->container->tag(self::class, [self::class]);
        $this->container->untag(ServiceNotFoundException::class, [self::class]);
    }
}
