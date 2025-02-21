<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Exception\BindingNotFoundException;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Name\Service;

use function sprintf;

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
        private array $list = [],
    ) {}

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

    public function clear(): void
    {
        $this->list = [];
    }

    public function contains(string $service): bool
    {
        $serviceName = Service::new($service)->toString();

        foreach ($this->list as $services) {
            foreach ($services as $service => $implementation) {
                if ($service !== $serviceName && $serviceName !== $implementation) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @template TGet of object
     *
     * @throws BindingNotFoundException
     *
     * @return class-string<TImplementation>
     *
     */
    public function get(string $concrete, string $service): string
    {
        $concreteName = Service::new($concrete);
        $serviceName = Service::new($service);

        return $this->list[$concreteName->toString()][$serviceName->toString()] ?? throw new BindingNotFoundException(
            sprintf('Binding not found for %s in %s', $serviceName->toString(), $concreteName->toString())
        );
    }

    /**
     * @template THas of object
     *
     */
    public function has(string $concrete, string $service): bool
    {
        $concreteName = Service::new($concrete);
        $serviceName = Service::new($service);

        return isset($this->list[$concreteName->toString()][$serviceName->toString()]);
    }

    public function set(string $concrete, string $service, string $implementation): void
    {
        $concreteName = Service::new($concrete);
        $serviceName = Service::new($service);
        $implementationName = Service::new($implementation);

        // when $concreteName needs $serviceName give it $implementation
        $this->list[$concreteName->toString()][$serviceName->toString()] = $implementationName->toString();
    }
}
