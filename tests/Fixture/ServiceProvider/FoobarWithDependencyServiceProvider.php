<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\ServiceProvider;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\ContainerTests\Fixture\Bar;
use Ghostwriter\ContainerTests\Fixture\Baz;
use Ghostwriter\ContainerTests\Fixture\Dummy;
use Ghostwriter\ContainerTests\Fixture\Extension\FoobarExtension;
use Ghostwriter\ContainerTests\Fixture\Foo;
use Ghostwriter\ContainerTests\Fixture\Foobar;
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
