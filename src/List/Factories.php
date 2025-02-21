<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Exception\FactoryNotFoundException;
use Ghostwriter\Container\Interface\FactoryInterface;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Name\Factory;
use Ghostwriter\Container\Name\Service;

use function array_key_exists;

/**
 * @template-covariant TService of object
 */
final class Factories implements ListInterface
{
    /**
     * @param array<class-string<TService>,class-string<FactoryInterface<TService>>> $list
     */
    public function __construct(
        private array $list = [],
    ) {}

    /**
     * @template TNewFactory of object
     *
     * @param array<class-string<TNewFactory>,class-string<FactoryInterface<TNewFactory>>> $list
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
     * @template TGetService of object
     *
     * @throws FactoryNotFoundException
     *
     * @return class-string<FactoryInterface<TGetService>>
     */
    public function get(string $service): string
    {
        return $this->list[Service::new($service)->toString()] ?? throw new FactoryNotFoundException($service);
    }

    public function has(string $service): bool
    {
        return array_key_exists(Service::new($service)->toString(), $this->list);
    }

    public function set(string $service, string $factory): void
    {
        $this->list[Service::new($service)->toString()] = Factory::new($factory)->toString();
    }

    public function unset(string $service): void
    {
        unset($this->list[Service::new($service)->toString()]);
    }
}
