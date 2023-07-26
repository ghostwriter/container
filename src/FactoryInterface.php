<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

/**
 * A factory is a callable object that creates an object using the given container instance.
 *
 * @template TService of object
 */
interface FactoryInterface
{
    /**
     * Create a service instance using the given container.
     *
     * @return TService
     */
    public function __invoke(ContainerInterface $container): object;
}
