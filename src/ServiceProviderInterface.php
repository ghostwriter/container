<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

interface ServiceProviderInterface
{
    /**
     * Registers a service on the given container.
     */
    public function __invoke(ContainerInterface $container): void;
}
