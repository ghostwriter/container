<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Service;

use Ghostwriter\Container\Interface\ContainerInterface;

/**
 * A factory is an invokable class that creates an object using the given container instance.
 *
 * @template TService of object
 */
interface FactoryInterface
{
    /** @return TService */
    public function __invoke(ContainerInterface $container): object;
}
