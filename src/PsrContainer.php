<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Interface\ContainerInterface;
use Override;

final readonly class PsrContainer implements \Psr\Container\ContainerInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    #[Override]
    public function get(string $id)
    {
        return $this->container->get($id);
    }

    #[Override]
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
