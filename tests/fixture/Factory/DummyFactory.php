<?php

declare(strict_types=1);

namespace Tests\Fixture\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;
use Tests\Fixture\Dummy;
use Tests\Fixture\DummyInterface;

/**
 * @implements FactoryInterface<DummyInterface>
 */
final readonly class DummyFactory implements FactoryInterface
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     */
    #[Override]
    public function __invoke(ContainerInterface $container): DummyInterface
    {
        return new Dummy($this);
    }
}
