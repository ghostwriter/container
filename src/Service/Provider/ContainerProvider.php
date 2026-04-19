<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Service\Provider;

use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\PsrContainer;
use Override;
use Psr\Container\ContainerInterface;
use Throwable;

final class ContainerProvider extends AbstractProvider
{
    /** @throws Throwable */
    #[Override]
    public function register(BuilderInterface $builder): void
    {
        $builder->alias(ContainerInterface::class, PsrContainer::class);
    }
}
