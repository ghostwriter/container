<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Exception\InstanceNotFoundException;
use Ghostwriter\Container\Interface\ListInterface;

/**
 * @template TService of object
 */
final class Instances implements ListInterface
{
    /**
     * @param array<class-string<TService>,TService> $list
     */
    public function __construct(
        private array $list = []
    ) {
    }

    /**
     * @template TNewService of object
     *
     * @param array<class-string<TNewService>,TNewService> $list
     *
     * @return self<TNewService>
     */
    public static function new(array $list = []): self
    {
        return new self($list);
    }

    /**
     * @param class-string<TService> $service
     *
     * @throws InstanceNotFoundException
     *
     * @return TService
     *
     */
    public function get(string $service): object
    {
        return $this->list[$service] ?? throw new InstanceNotFoundException($service);
    }

    /**
     * @param class-string<TService> $service
     *
     * @psalm-assert-if-true class-string<TService> $this->list[$service]
     */
    public function has(string $service): bool
    {
        return \array_key_exists($service, $this->list);
    }

    /**
     * @template TSet of object
     *
     * @param class-string<TSet> $service
     * @param TSet               $value
     */
    public function set(string $service, object $value): void
    {
        /** @var self<TService|TSet> $this */
        $this->list[$service] = $value;
    }

    /**
     * @param class-string<TService> $service
     */
    public function unset(string $service): void
    {
        unset($this->list[$service]);
    }
}
