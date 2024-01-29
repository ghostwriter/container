<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture;

use Ghostwriter\Container\Interface\ContainerInterface;
use stdClass;

final readonly class InvalidStdClassFactory
{

    public function __invoke(ContainerInterface $container, array $arguments = []): stdClass
    {
        return new stdClass();
    }
}
