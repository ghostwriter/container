<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\FactoryInterface;

final readonly class DummyFactory implements FactoryInterface
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, array $arguments = []): DummyInterface
    {
        return $container->get(Dummy::class);
    }
}
