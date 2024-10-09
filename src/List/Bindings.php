<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Exception\BindingNotFoundException;
use Ghostwriter\Container\Interface\ListInterface;

/**
 * @template-covariant TConcrete of object
 * @template-covariant TService of object
 * @template-covariant TImplementation of object
 */
final class Bindings implements ListInterface
{
    /**
     * @param array<class-string<TConcrete>,non-empty-array<class-string<TService>,class-string<TImplementation>>> $list
     */
    public function __construct(
        private array $list = []
    ) {
    }

    /**
     * @template TNewConcrete of object
     * @template TNewService of object
     * @template TNewImplementation of object
     *
     * @param array<class-string<TNewConcrete>,non-empty-array<class-string<TNewService>,class-string<TNewImplementation>>> $list
     */
    public static function new(array $list = []): self
    {
        return new self($list);
    }

    public function contains(string $service): bool
    {
        foreach ($this->list as $services) {
            if (\in_array($service, $services, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template TGet of object
     *
     * @param class-string<TGet> $service
     *
     * @throws BindingNotFoundException
     *
     * @return class-string<TImplementation>
     *
     */
    public function get(string $concrete, string $service): string
    {
        return $this->list[$concrete][$service] ?? throw new BindingNotFoundException();
    }

    /**
     * @template THas of object
     *
     * @param class-string<THas> $service
     */
    public function has(string $concrete, string $service): bool
    {
        if (! \array_key_exists($concrete, $this->list)) {
            return false;
        }

        return \array_key_exists($service, $this->list[$concrete]);
    }

    /**
     * @template TSetConcrete of object
     * @template TSetService of object
     * @template TSetImplementation of object
     *
     * @param class-string<TSetConcrete>       $concrete
     * @param class-string<TSetService>        $service
     * @param class-string<TSetImplementation> $implementation
     */
    public function set(string $concrete, string $service, string $implementation): void
    {
        $this->list[$concrete][$service] = $implementation;
    }
}
