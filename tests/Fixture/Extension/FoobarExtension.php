<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\Extension;

use Ghostwriter\Container\ContainerInterface;
use Ghostwriter\Container\ExtensionInterface;
use Ghostwriter\Container\Tests\Fixture\Bar;
use Ghostwriter\Container\Tests\Fixture\Foo;

class FoobarExtension implements ExtensionInterface
{
    public function __invoke(ContainerInterface $container, object $service): object
    {
        $service->foo = $container->get(Foo::class);

        $service->bar = $container->get(Bar::class);

        return $service;
    }
}
