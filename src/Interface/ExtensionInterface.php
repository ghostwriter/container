<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

/**
 * An extension is an invokable object that extends an object; created using the given container instance.
 *
 * @template TService of object
 */
interface ExtensionInterface
{
    /**
     * Extend a service on the given container.
     *
     * @param ContainerInterface $container
     * @param TService $service
     *
     * @return TService
     */
    public function __invoke(ContainerInterface $container, object $service): object;
}
