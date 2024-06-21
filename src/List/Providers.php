<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;

use function array_key_exists;

final class Providers implements ListInterface
{
    /**
     * @param array<class-string<ServiceProviderInterface>,bool> $list
     */
    public function __construct(
        private array $list = []
    ) {
    }

    /**
     * @param class-string<ServiceProviderInterface> $serviceProvider
     */
    public function add(string $serviceProvider): void
    {
        $this->list[$serviceProvider] = true;
    }

    /**
     * @param class-string<ServiceProviderInterface> $serviceProvider
     */
    public function has(string $serviceProvider): bool
    {
        return array_key_exists($serviceProvider, $this->list);
    }

    public function unset(string $serviceProvider): void
    {
        unset($this->list[$serviceProvider]);
    }

    /**
     * @param array<class-string<ServiceProviderInterface>,bool> $list
     */
    public static function new(array $list = []): self
    {
        return new self($list);
    }
}
