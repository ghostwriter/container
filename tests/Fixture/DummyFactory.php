<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Tests\Fixture\Dummy;
use Ghostwriter\Container\Tests\Fixture\DummyInterface;

class DummyFactory
{
    public function __invoke(ContainerInterface $container): DummyInterface
    {
       return $container->get(Dummy::class);
    }
}
