<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\Container\Tests\Fixture\Bar;
use Ghostwriter\Container\Tests\Fixture\Foo;
use stdClass;

class StdClassTwoExtension implements ExtensionInterface
{
    public function __invoke(ContainerInterface $container, object $service): object
    {
        $service->two = $container->get(stdClass::class);

        return $service;
    }
}
