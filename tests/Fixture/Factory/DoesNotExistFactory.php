<?php

namespace Tests\Fixture\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;

final class DoesNotExistFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return $container->build('does-not-exist');
    }
}
