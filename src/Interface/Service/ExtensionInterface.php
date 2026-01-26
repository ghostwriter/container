<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Service;

use Ghostwriter\Container\Interface\ContainerInterface;

/**
 * An extension is an invokable class that extends a service from the given container instance.
 *
 * @template TService of object
 */
interface ExtensionInterface
{
    /**
     * Extend a service on the given container.
     *
     * @param TService $service
     */
    public function __invoke(ContainerInterface $container, object $service): void;
}
