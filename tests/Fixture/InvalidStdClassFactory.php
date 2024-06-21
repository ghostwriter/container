<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Ghostwriter\Container\Interface\ContainerInterface;
use stdClass;

final readonly class InvalidStdClassFactory /* Does not implement FactoryInterface */
{
    public function __invoke(ContainerInterface $container): stdClass
    {
        return new stdClass();
    }
}
