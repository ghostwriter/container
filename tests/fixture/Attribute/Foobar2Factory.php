<?php

namespace Tests\Fixture\Attribute;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;

final class Foobar2Factory implements FactoryInterface
{
    public function create(): Foobar2Interface
    {
        return new Foobar2();
    }

    /**
     * @param ContainerInterface $container
     * @return object
     */
    public function __invoke(ContainerInterface $container): object
    {
        return new Foobar2('foo');
    }
}
