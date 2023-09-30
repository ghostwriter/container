<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\ServiceProvider;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\Container\Tests\Fixture\Bar;
use Ghostwriter\Container\Tests\Fixture\Baz;
use Ghostwriter\Container\Tests\Fixture\Extension\FoobarExtension;
use Ghostwriter\Container\Tests\Fixture\Foo;
use stdClass;
use Throwable;

class FoobarServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws Throwable
     */
    public function __invoke(ContainerInterface $container): void
    {
        $container->bind('foobar', stdClass::class);
        $container->bind(Foo::class);
        $container->bind(Bar::class);
        $container->bind(Baz::class);
    }
}
