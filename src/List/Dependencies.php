<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Exception\DependencyNotFoundException;
use Ghostwriter\Container\Exception\ShouldNotHappenException;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Name\Service;

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
        private array $list = [],
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

    public function clear(): void
    {
        $this->list = [];
    }

    /**
     *
     * @psalm-assert-if-true class-string<TService> $this->list[$class]
     */
    public function has(string $service): bool
    {
        $serviceName = Service::new($service);

        return array_key_exists($serviceName->toString(), $this->list);
    }

    public function isEmpty(): bool
    {
        return [] === $this->list;
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
            throw new ShouldNotHappenException();
        }

        return array_key_last($this->list);
    }

    public function missing(): bool
    {
        return [] !== $this->list;
    }

    /**
     * @template TSet of object
     *
     */
    public function set(string $serviceName): void
    {
        /** @var self<TService|TSet> $this */
        $this->list[Service::new($serviceName)->toString()] = true;
    }

    /**
     * @return list<class-string<TService>>
     */
    public function toArray(): array
    {
        return array_keys($this->list);
    }

    public function unset(string $serviceName): void
    {
        unset($this->list[Service::new($serviceName)->toString()]);
    }
}
