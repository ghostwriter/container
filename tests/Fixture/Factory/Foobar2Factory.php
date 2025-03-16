<?php

namespace Tests\Fixture\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Tests\Fixture\Attribute\Factory\Foobar2;
use Tests\Fixture\Attribute\Foobar2Interface;

/**
 * @implements FactoryInterface<Foobar2Interface>
 */
final class Foobar2Factory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): Foobar2Interface
    {
        return new Foobar2('foo');
    }
}
