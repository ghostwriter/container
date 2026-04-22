<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Service\Provider;

use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Ghostwriter\Container\Interface\Service\ProviderInterface;
use Override;
use Throwable;

abstract class AbstractProvider implements ProviderInterface
{
    /** @var array<class-string,class-string> */
    protected const array ALIAS = [
        // alias => service
    ];

    /** @var array<class-string,list<class-string<ExtensionInterface>>> */
    protected const array EXTEND = [
        // service => [extension, ...]
    ];

    /** @var array<class-string,class-string<FactoryInterface>> */
    protected const array FACTORY = [
        // service => factory
    ];

    /** @throws Throwable */
    #[Override]
    public function boot(ContainerInterface $container): void
    {
        // no-op
    }

    /** @throws Throwable */
    #[Override]
    public function register(BuilderInterface $builder): void
    {
        foreach (static::ALIAS as $alias => $service) {
            $builder->alias($alias, $service);
        }

        foreach (static::EXTEND as $service => $extensions) {
            foreach ($extensions as $extension) {
                $builder->extend($service, $extension);
            }
        }

        foreach (static::FACTORY as $service => $factory) {
            $builder->factory($service, $factory);
        }
    }
}
