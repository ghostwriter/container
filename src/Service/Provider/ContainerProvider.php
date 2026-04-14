<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Service\Provider;

use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\PsrContainer;
use Override;
use Throwable;

final class ContainerProvider extends AbstractProvider
{
    /** @throws Throwable */
    #[Override]
    public function register(BuilderInterface $builder): void
    {
        $builder->alias(\Psr\Container\ContainerInterface::class, PsrContainer::class);
    }
}
