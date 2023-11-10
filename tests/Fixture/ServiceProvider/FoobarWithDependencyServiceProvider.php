<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\ServiceProvider;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\Container\Tests\Fixture\Bar;
use Ghostwriter\Container\Tests\Fixture\Baz;
use Ghostwriter\Container\Tests\Fixture\Dummy;
use Ghostwriter\Container\Tests\Fixture\Extension\FoobarExtension;
use Ghostwriter\Container\Tests\Fixture\Foo;
use Ghostwriter\Container\Tests\Fixture\Foobar;
use stdClass;
use Throwable;

class FoobarWithDependencyServiceProvider implements ServiceProviderInterface
{
    private Dummy $dummy;
    public function __construct(Dummy $dummy)
    {
        $this->dummy = $dummy;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(ContainerInterface $container): void
    {
        $container->set(Dummy::class, $this->dummy);
        $container->register(Foobar::class, stdClass::class);
        $container->register(Foo::class);
        $container->register(Bar::class);
        $container->register(Baz::class);
        $container->set(Foobar::class, $container->get(FoobarExtension::class));
    }
}
