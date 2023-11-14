<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use stdClass;

final readonly class StdClassFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, array $arguments = []): stdClass
    {
        $object = new stdClass();

        $object->blackLivesMatter = '#FreePalestine';

        return $object;
    }
}
