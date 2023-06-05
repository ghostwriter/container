<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

/**
 * An extension is an invokable object that extends an object; created using the given container instance.
 *
 * @template TObject of object
 */
interface ExtensionInterface
{
    /**
     * Extend a service on the given container.
     *
     * @return TObject
     */
    public function __invoke(ContainerInterface $container, object $service): object;
}
