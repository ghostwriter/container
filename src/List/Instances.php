<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Exception\InstanceNotFoundException;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Name\Service;

use function array_key_exists;

/**
 * @template TService of object
 */
final class Instances implements ListInterface
{
    /**
     * @param array<class-string<TService>,TService> $list
     */
    public function __construct(
        private array $list = [],
    ) {}

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

    public function clear(): void
    {
        $this->list = [];
    }

    /**
     * @throws InstanceNotFoundException
     *
     * @return TService
     */
    public function get(string $service): object
    {
        return $this->list[Service::new($service)->toString()]
            ?? throw new InstanceNotFoundException($service);
    }

    /**
     * @psalm-assert-if-true class-string<TService> $this->list[$service]
     */
    public function has(string $service): bool
    {
        return array_key_exists(Service::new($service)->toString(), $this->list);
    }

    /**
     * @template TSet of object
     *
     * @param class-string<TSet> $service
     * @param TSet               $value
     */
    public function set(string $service, object $value): void
    {
        /** @var self<class-string<TSet>,TSet> $this */
        $this->list[Service::new($service)->toString()] = $value;
    }

    public function unset(string $service): void
    {
        unset($this->list[Service::new($service)->toString()]);
    }
}
