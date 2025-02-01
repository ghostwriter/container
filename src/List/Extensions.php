<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Container\Interface\ListInterface;

/**
 * @template-covariant TService of object
 */
final class Extensions implements ListInterface
{
    /**
     * @param array<class-string<TService>,non-empty-list<class-string<ExtensionInterface<TService>>,bool>> $list
     */
    public function __construct(
        private array $list = []
    ) {}

    /**
     * @template TNewService of object
     *
     * @param array<class-string<TNewService>,non-empty-list<class-string<ExtensionInterface<TNewService>>,bool>> $list
     */
    public static function new(array $list = []): self
    {
        return new self($list);
    }

    /**
     * @return array<class-string<TService>,list<class-string<ExtensionInterface<TService>>,bool>>
     */
    public function all(): array
    {
        return $this->list;
    }

    /**
     * @template TSet of object
     *
     * @param class-string<TSet>                     $service
     * @param class-string<ExtensionInterface<TSet>> $extension
     */
    public function set(string $service, string $extension): void
    {
        /** @var self<TService|TSet> $this */
        $this->list[$service][$extension] = true;
    }

    /**
     * @param class-string<TService> $service
     */
    public function unset(string $service): void
    {
        unset($this->list[$service]);
    }
}
