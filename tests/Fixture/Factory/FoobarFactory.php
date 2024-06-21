<?php

declare(strict_types=1);

namespace Tests\Fixture\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;
use Tests\Fixture\Foobar;
use Throwable;

use function time;

/**
 * @implements FactoryInterface<Foobar>
 */
final readonly class FoobarFactory implements FactoryInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): Foobar
    {
        return new Foobar(time());
    }
}
