<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\ServiceProvider;

use Ghostwriter\Container\ContainerInterface;
use Ghostwriter\Container\ServiceProviderInterface;
use Ghostwriter\Container\Tests\Fixture\Bar;
use Ghostwriter\Container\Tests\Fixture\Baz;
use Ghostwriter\Container\Tests\Fixture\Dummy;
use Ghostwriter\Container\Tests\Fixture\Extension\FoobarExtension;
use Ghostwriter\Container\Tests\Fixture\Foo;
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
        $container->set('dummy', $this->dummy);
        $container->bind('foobar', stdClass::class);
        $container->bind(Foo::class);
        $container->bind(Bar::class);
        $container->bind(Baz::class);
        $container->add('foobar', $container->get(FoobarExtension::class));
    }
}
