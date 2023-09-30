<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;

class DummyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): DummyInterface
    {
       return $container->get(Dummy::class);
    }
}
