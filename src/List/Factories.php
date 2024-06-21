<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Exception\FactoryNotFoundException;
use Ghostwriter\Container\Interface\FactoryInterface;
use Ghostwriter\Container\Interface\ListInterface;

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
        private array $list = []
    ) {
    }

    /**
     * @param class-string<TService> $service
     *
     * @return class-string<FactoryInterface<TService>>
     */
    public function get(string $service): string
    {
        return $this->list[$service] ?? throw new FactoryNotFoundException($service);
    }

    /**
     * @param class-string<TService> $service
     */
    public function has(string $service): bool
    {
        return array_key_exists($service, $this->list);
    }

    /**
     * @template TSet of object
     *
     * @param class-string<TSet>                   $service
     * @param class-string<FactoryInterface<TSet>> $factory
     */
    public function set(string $service, string $factory): void
    {
        /** @var self<TService|TSet> $this */
        $this->list[$service] = $factory;
    }

    /**
     * @param class-string<TService> $service
     */
    public function unset(string $service): void
    {
        unset($this->list[$service]);
    }

    /**
     * @template TNewFactory of object
     *
     * @param array<class-string<TNewFactory>,class-string<FactoryInterface<TNewFactory>>> $list
     */
    public static function new(array $list = []): self
    {
        return new self($list);
    }
}
