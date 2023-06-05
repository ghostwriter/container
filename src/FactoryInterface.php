<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Contract;

/**
 * A factory is a callable object that creates an object using the given container instance.
 *
 * @template T of object
 */
interface FactoryInterface
{
    /**
     * Create a service instance using the given container.
     *
     * @return T
     */
    public function __invoke(ContainerInterface $container): object;
}
