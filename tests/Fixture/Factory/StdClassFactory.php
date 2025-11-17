<?php

declare(strict_types=1);

namespace Tests\Fixture\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Override;
use stdClass;

final readonly class StdClassFactory implements FactoryInterface
{
    #[Override]
    public function __invoke(ContainerInterface $container): stdClass
    {
        $object = new stdClass();

        $object->blackLivesMatter = '#FreePalestine';

        return $object;
    }
}
