<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Service\Provider;

use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ProviderInterface;
use Ghostwriter\Container\PsrContainer;
use Override;
use Throwable;

final class ContainerProvider implements ProviderInterface
{
    /** @throws Throwable */
    #[Override]
    public function boot(ContainerInterface $container): void
    {
        // no-op
    }

    /** @throws Throwable */
    #[Override]
    public function register(BuilderInterface $builder): void
    {
        $builder->alias(\Psr\Container\ContainerInterface::class, PsrContainer::class);
    }
}
