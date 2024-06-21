<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

/**
 * A factory is a callable object that creates an object using the given container instance.
 *
 * @template-covariant TService of object
 */
interface FactoryInterface
{
    /**
     * @return TService
     */
    public function __invoke(ContainerInterface $container): object;
}
