<?php

declare(strict_types=1);

namespace Tests\Fixture\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\ContainerNotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Override;
use Tests\Fixture\Dummy;
use Tests\Fixture\DummyInterface;

/**
 * @implements FactoryInterface<DummyInterface>
 */
final readonly class DummyFactory implements FactoryInterface
{
    /**
     * @throws ContainerNotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Override]
    public function __invoke(ContainerInterface $container): DummyInterface
    {
        return new Dummy($this);
    }
}
