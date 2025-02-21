<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Generator;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Name\Service;
use Ghostwriter\Container\Name\Tag;

use function array_key_exists;
use function array_keys;

/**
 * @template-covariant TService of object
 */
final class Tags implements ListInterface
{
    /**
     * @param array<non-empty-string,non-empty-array<class-string<TService>,bool>> $list
     */
    public function __construct(
        private array $list = [],
    ) {}

    /**
     * @template TNewService of object
     *
     * @param array<non-empty-string,non-empty-array<class-string<TNewService>,bool>> $list
     */
    public static function new(array $list = []): self
    {
        return new self($list);
    }

    public function clear(): void
    {
        $this->list = [];
    }

    /**
     * @throws ServiceTagNotFoundException
     *
     * @return Generator<class-string<TService>>
     */
    public function get(string $tag): Generator
    {
        $tag = Tag::new($tag)->toString();

        if (! array_key_exists($tag, $this->list)) {
            throw new ServiceTagNotFoundException($tag);
        }

        yield from array_keys($this->list[$tag]);
    }

    public function has(string $tag): bool
    {
        return array_key_exists(Tag::new($tag)->toString(), $this->list);
    }

    /**
     * @param list<non-empty-string> $tags
     */
    public function remove(string $service, array $tags = []): void
    {
        $service = Service::new($service)->toString();

        foreach ($tags as $tag) {
            if (! array_key_exists($tag, $this->list)) {
                throw new ServiceTagNotFoundException($tag);
            }

            if (! array_key_exists($service, $this->list[$tag])) {
                throw new ServiceNotFoundException($tag);
            }

            unset($this->list[$tag][$service]);
        }
    }

    /**
     * @template TSet of object
     *
     * @param non-empty-list<non-empty-string> $tags
     */
    public function set(string $service, array $tags): void
    {
        $serviceName = Service::new($service);

        $service = $serviceName->toString();

        foreach ($tags as $tag) {
            /** @var self<TService|TSet> $this */
            $this->list[Tag::new($tag)->toString()][$service] = true;
        }
    }

    public function unset(string $service): void
    {
        $service = Service::new($service)->toString();

        foreach ($this->list as $tag => $services) {
            if (! array_key_exists($service, $services)) {
                continue;
            }

            unset($this->list[$tag][$service]);

            if ([] === $this->list[$tag]) {
                unset($this->list[$tag]);
            }
        }
    }
}
