<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

/**
 * A factory is a callable object that creates an object using the given container instance.
 *
 * @template TService of object
 *
 * @return TService
 */
interface FactoryInterface
{
    /**
     * Create a service instance using the given container.
     *
     * @template TArgument
     *
     * @param array<TArgument> $arguments
     *
     * @return TService
     */
    public function __invoke(ContainerInterface $container, array $arguments = []): object;
}
