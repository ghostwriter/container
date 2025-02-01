<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Exception\DependencyNotFoundException;
use Ghostwriter\Container\Interface\ListInterface;

use function array_key_exists;
use function array_key_last;
use function array_keys;

/**
 * @template-covariant TService of object
 */
final class Dependencies implements ListInterface
{
    /**
     * @param array<class-string<TService>,bool> $list
     */
    public function __construct(
        private array $list = []
    ) {}

    /**
     * @template TNewService of object
     *
     * @param array<class-string<TNewService>,bool> $list
     */
    public static function new(array $list = []): self
    {
        return new self($list);
    }

    public function found(): bool
    {
        return [] !== $this->list;
    }

    /**
     * @param class-string<TService> $class
     *
     * @psalm-assert-if-true class-string<TService> $this->list[$class]
     */
    public function has(string $class): bool
    {
        return array_key_exists($class, $this->list);
    }

    /**
     * @throws DependencyNotFoundException
     *
     * @return class-string<TService>
     *
     */
    public function last(): string
    {
        if ([] === $this->list) {
            throw new DependencyNotFoundException();
        }

        return array_key_last($this->list);
    }

    /**
     * @template TSet of object
     *
     * @param class-string<TSet> $class
     */
    public function set(string $class): void
    {
        /** @var self<TService|TSet> $this */
        $this->list[$class] = true;
    }

    /**
     * @return list<class-string<TService>>
     */
    public function toArray(): array
    {
        return array_keys($this->list);
    }

    /**
     * @param class-string<TService> $class
     */
    public function unset(string $class): void
    {
        unset($this->list[$class]);
    }
}
