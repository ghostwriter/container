<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Name\Extension;
use Ghostwriter\Container\Name\Service;

/**
 * @template-covariant TService of object
 */
final class Extensions implements ListInterface
{
    /**
     * @param array<class-string<TService>,non-empty-list<class-string<ExtensionInterface<TService>>,bool>> $list
     */
    public function __construct(
        private array $list = [],
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

    public function clear(): void
    {
        $this->list = [];
    }

    public function set(string $service, string $extension): void
    {
        $this->list[Service::new($service)->toString()][Extension::new($extension)->toString()] = true;
    }

    public function unset(string $service): void
    {
        unset($this->list[Service::new($service)->toString()]);
    }
}
