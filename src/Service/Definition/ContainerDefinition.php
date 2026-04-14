<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Service\Definition;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\PsrContainer;
use Override;
use Throwable;

final class ContainerDefinition implements DefinitionInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $container->alias(\Psr\Container\ContainerInterface::class, PsrContainer::class);
    }
}
