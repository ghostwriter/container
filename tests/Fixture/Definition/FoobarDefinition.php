<?php

declare(strict_types=1);

namespace Tests\Fixture\Definition;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Override;
use stdClass;
use Tests\Fixture\Bar;
use Tests\Fixture\Baz;
use Tests\Fixture\Foo;
use Tests\Fixture\Foobar;
use Throwable;

final readonly class FoobarDefinition implements DefinitionInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $container->alias(stdClass::class, Foobar::class);
        $container->set(Foo::class, $container->build(Foo::class));
        $container->set(Bar::class, $container->build(Bar::class));
        $container->set(Baz::class, $container->build(Baz::class));
    }
}
