<?php

declare(strict_types=1);

namespace Tests\Fixture\Definition;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Override;
use stdClass;
use Tests\Fixture\Bar;
use Tests\Fixture\Baz;
use Tests\Fixture\Dummy;
use Tests\Fixture\Foo;
use Tests\Fixture\Foobar;
use Throwable;

final readonly class FoobarWithDependencyDefinition implements DefinitionInterface
{
    private Dummy $dummy;

    public function __construct(Dummy $dummy)
    {
        $this->dummy = $dummy;
    }

    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $container->set(Dummy::class, $this->dummy);
        $container->alias(stdClass::class, Foobar::class);
        $container->set(Foo::class, $container->get(Foo::class));
        $container->set(Bar::class, $container->get(Bar::class));
        $container->set(Baz::class, $container->get(Baz::class));
        $container->set(Foobar::class, $container->get(Foobar::class));
    }
}
