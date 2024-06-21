<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Generator;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
use Ghostwriter\Container\Interface\ListInterface;

use function array_key_exists;
use function array_keys;
use function trim;

/**
 * @template-covariant TService of object
 */
final class Tags implements ListInterface
{
    /**
     * @param array<non-empty-string,non-empty-array<class-string<TService>,bool>> $list
     */
    public function __construct(
        private array $list = []
    ) {
    }

    /**
     * @param non-empty-string $tag
     *
     * @throws ServiceTagNotFoundException
     *
     * @return Generator<class-string<TService>>
     *
     *
     */
    public function get(string $tag): Generator
    {
        if (! array_key_exists($tag, $this->list)) {
            throw new ServiceTagNotFoundException($tag);
        }

        yield from array_keys($this->list[$tag]);
    }

    /**
     * @param class-string<TService>  $service
     * @param array<non-empty-string> $tags
     */
    public function remove(string $service, array $tags = []): void
    {
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
     * @param class-string<TSet>                $service
     * @param non-empty-array<non-empty-string> $tags
     */
    public function set(string $service, array $tags): void
    {
        foreach ($tags as $tag) {
            if (trim($tag) === '') {
                throw new ServiceTagMustBeNonEmptyStringException();
            }

            /** @var self<TService|TSet> $this */
            $this->list[$tag][$service] = true;
        }
    }

    /**
     * @param class-string<TService> $service
     */
    public function unset(string $service): void
    {
        foreach ($this->list as $tag => $services) {
            if (! array_key_exists($service, $services)) {
                continue;
            }

            unset($this->list[$tag][$service]);
        }
    }

    /**
     * @template TNewService of object
     *
     * @param array<non-empty-string,non-empty-array<class-string<TNewService>,bool>> $list
     */
    public static function new(array $list = []): self
    {
        return new self($list);
    }
}
