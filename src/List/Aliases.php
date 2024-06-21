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

use function array_key_exists;
use function trim;

/**
 * @template-covariant TAlias of object
 * @template-covariant TService of object
 */
final class Aliases implements ListInterface
{
    /**
     * @param non-empty-array<class-string<TAlias>,class-string<TService>> $list
     */
    public function __construct(
        private array $list
    ) {
    }

    /**
     * @param class-string<TAlias> $alias
     *
     * @return class-string<TService>
     */
    public function get(string $alias): string
    {
        return $this->list[$alias] ?? throw new AliasNotFoundException($alias);
    }

    /**
     * @param class-string<TAlias> $alias
     *
     * @psalm-assert-if-true class-string<TAlias> $this->list[$alias]
     */
    public function has(string $alias): bool
    {
        return array_key_exists($alias, $this->list);
    }

    /**
     * @template TSetAlias of object
     * @template TSetService of object
     *
     * @param class-string<TSetAlias>   $alias
     * @param class-string<TSetService> $service
     *
     * @throws AliasNameMustBeNonEmptyStringException
     * @throws ServiceNameMustBeNonEmptyStringException
     * @throws AliasNameAndServiceNameCannotBeTheSameException
     */
    public function set(string $alias, string $service): void
    {
        if (trim($alias) === '') {
            throw new AliasNameMustBeNonEmptyStringException();
        }

        if (trim($service) === '') {
            throw new ServiceNameMustBeNonEmptyStringException();
        }

        if ($alias === $service) {
            throw new AliasNameAndServiceNameCannotBeTheSameException($alias);
        }

        /** @var self<TAlias|TSetAlias,TService|TSetService> $this */
        $this->list[$alias] = $service;
    }

    /**
     * @param class-string<TAlias> $alias
     */
    public function unset(string $alias): void
    {
        unset($this->list[$alias]);
    }

    /**
     * @template TNewAlias of object
     * @template TNewService of object
     *
     * @param non-empty-array<class-string<TNewAlias>,class-string<TNewService>> $list
     *
     * @return self<TNewAlias,TNewService>
     */
    public static function new(array $list = [
        ContainerInterface::class => Container::class,
    ]): self
    {
        return new self($list);
    }
}
