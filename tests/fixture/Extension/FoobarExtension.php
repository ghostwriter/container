<?php

declare(strict_types=1);

namespace Tests\Fixture\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Override;
use Tests\Fixture\Bar;
use Tests\Fixture\Foo;
use Tests\Fixture\Foobar;
use Throwable;

/**
 * @implements ExtensionInterface<Foobar>
 */
final readonly class FoobarExtension implements ExtensionInterface
{
    /**
     * @param Foobar $service
     *
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container, object $service): Foobar
    {
        $service->foo = $container->get(Foo::class);

        $service->bar = $container->get(Bar::class);

        return $service;
    }
}
