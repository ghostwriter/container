<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\Container\Name\Provider;

use function array_key_exists;

final class Providers implements ListInterface
{
    /**
     * @param array<class-string<ServiceProviderInterface>,bool> $list
     */
    public function __construct(
        private array $list = [],
    ) {}

    public static function new(): self
    {
        return new self();
    }

    public function add(string $provider, ContainerInterface $container): void
    {
        $this->list[Provider::new($provider)->toString()] = true;

        $container->invoke($provider);
    }

    public function clear(): void
    {
        $this->list = [];
    }

    public function has(string $provider): bool
    {
        return array_key_exists(Provider::new($provider)->toString(), $this->list);
    }
}
