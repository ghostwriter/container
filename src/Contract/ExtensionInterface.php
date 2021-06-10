<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Contract;

interface ExtensionInterface
{
    /**
     * Extends a service on the given container.
     */
    public function __invoke(ContainerInterface $container, object $service): object;
}
