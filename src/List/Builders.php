<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Closure;
use Ghostwriter\Container\Exception\BuilderNotFoundException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ListInterface;

/**
 * @template-covariant TService of object
 */
final class Builders implements ListInterface
{
    /**
     * @param array<class-string<TService>,Closure(ContainerInterface):TService> $list
     */
    public function __construct(
        private array $list = []
    ) {
    }

    /**
     * @template TNewService of object
     *
     * @param array<class-string<TNewService>,Closure(ContainerInterface):TNewService> $list
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
     * @throws BuilderNotFoundException
     *
     * @return Closure(ContainerInterface):TService
     *
     */
    public function get(string $service): Closure
    {
        return $this->list[$service] ?? throw new BuilderNotFoundException($service);
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
     * @param class-string<TSet>               $service
     * @param Closure(ContainerInterface):TSet $value
     */
    public function set(string $service, Closure $value): void
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
