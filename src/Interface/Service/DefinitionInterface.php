<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Service;

use Ghostwriter\Container\Interface\ContainerInterface;

/**
 * A provider is an invokable class that registers services on the given container instance.
 */
interface DefinitionInterface
{
    public function __invoke(ContainerInterface $container): void;
}
