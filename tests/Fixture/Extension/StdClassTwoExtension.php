<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use stdClass;

class StdClassTwoExtension implements ExtensionInterface
{
    public function __invoke(ContainerInterface $container, object $service): object
    {
        $service->two = $container->get(stdClass::class);

        return $service;
    }
}
