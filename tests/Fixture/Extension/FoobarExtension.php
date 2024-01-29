<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\ContainerTests\Fixture\Bar;
use Ghostwriter\ContainerTests\Fixture\Foo;

class FoobarExtension implements ExtensionInterface
{
    public function __invoke(ContainerInterface $container, object $service): object
    {
        $service->foo = $container->get(Foo::class);

        $service->bar = $container->get(Bar::class);

        return $service;
    }
}
