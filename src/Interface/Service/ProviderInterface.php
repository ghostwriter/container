<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Service;

use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Throwable;

interface ProviderInterface
{
    /** @throws Throwable */
    public function boot(ContainerInterface $container): void;

    /** @throws Throwable */
    public function register(BuilderInterface $builder): void;
}
