<?php

declare(strict_types=1);

namespace Tests\Fixture\ServiceProvider;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Override;
use stdClass;
use Tests\Fixture\Bar;
use Tests\Fixture\Baz;
use Tests\Fixture\Foo;
use Tests\Fixture\Foobar;
use Throwable;

final readonly class FoobarServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $container->register(Foobar::class, stdClass::class);
        $container->register(Foo::class);
        $container->register(Bar::class);
        $container->register(Baz::class);
    }
}
