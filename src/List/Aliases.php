<?php

declare(strict_types=1);

namespace Ghostwriter\Container\List;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\AliasNotFoundException;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ListInterface;
use Ghostwriter\Container\Name\Alias;
use Ghostwriter\Container\Name\Service;
use Ghostwriter\Container\PsrContainer;
use Psr\Container\ContainerInterface as PsrContainerInterface;

use function in_array;

/**
 * @template-covariant TAlias of object
 * @template-covariant TService of object
 */
final class Aliases implements ListInterface
{
    /**
     * @var array<TService,TAlias>
     */
    public const array DEFAULT = [
        Container::class => ContainerInterface::class,
        PsrContainer::class => PsrContainerInterface::class,
    ];

    /**
     * @param array<TService,TAlias> $list
     */
    public function __construct(
        private array $list = self::DEFAULT,
    ) {}

    public static function new(): self
    {
        return new self();
    }

    public function clear(): void
    {
        $this->list = self::DEFAULT;
    }

    /**
     * @return class-string<TService>
     */
    public function get(string $service): string
    {
        $alias = Alias::new($service)->toString();

        foreach ($this->list as $serviceName => $aliasName) {
            if ($aliasName === $alias) {
                return $serviceName;
            }
        }

        throw new AliasNotFoundException($service);
    }

    public function has(string $service): bool
    {
        return in_array(Service::new($service)->toString(), $this->list, true);
    }

    /**
     * @template TSetAlias of object
     * @template TSetService of object
     *
     * @throws AliasNameMustBeNonEmptyStringException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws AliasNameAndServiceNameCannotBeTheSameException
     */
    public function set(string $service, string $alias): void
    {
        $alias = Alias::new($alias)->toString();

        $service = Service::new($service)->toString();

        if ($alias === $service) {
            throw new AliasNameAndServiceNameCannotBeTheSameException($alias);
        }

        /** @var self<TAlias|TSetAlias,TService|TSetService> $this */
        $this->list[$service] = $alias;
    }

    public function unset(string $service): void
    {
        unset($this->list[Service::new($service)->toString()]);
    }
}
