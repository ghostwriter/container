<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Closure;
use Ghostwriter\Container\Exception\BuilderAlreadyExistsException;
use Ghostwriter\Container\Exception\BuilderNotFoundException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Name\Service;

use function array_key_exists;

/**
 * @template-covariant TService of object
 */
final class Builders implements ListInterface
{
    /**
     * @param Closure $list
     */
    public function __construct(
        private array $list = [],
    ) {}

    /**
     * @template TNewService of object
     *
     * @param Closure $list
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
     * @param class-string<TService> $service
     *
     * @throws BuilderNotFoundException
     *
     * @return Closure(ContainerInterface):TService
     */
    public function get(string $service): Closure
    {
        return $this->list[Service::new($service)->toString()]
            ?? throw new BuilderNotFoundException($service);
    }

    /**
     * @param class-string<TService> $service
     */
    public function has(string $service): bool
    {
        return array_key_exists(Service::new($service)->toString(), $this->list);
    }

    /**
     * @template TSet of object
     *
     * @param Closure(ContainerInterface):TSet $value
     */
    public function set(string $service, Closure $value): void
    {
        if ($this->has($service)) {
            throw new BuilderAlreadyExistsException($service);
        }

        /** @var self<TService|TSet> $this */
        $this->list[Service::new($service)->toString()] = $value;
    }

    /**
     * @param class-string<TService> $service
     */
    public function unset(string $service): void
    {
        unset($this->list[Service::new($service)->toString()]);
    }
}
