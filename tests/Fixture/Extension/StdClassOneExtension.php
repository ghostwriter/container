<?php

declare(strict_types=1);

namespace Tests\Fixture\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Override;
use stdClass;
use Throwable;

/**
 * @implements ExtensionInterface<stdClass>
 */
final readonly class StdClassOneExtension implements ExtensionInterface
{
    /**
     * @param stdClass $service
     *
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container, object $service): void
    {
        $service->one = $container->get(stdClass::class);
    }
}
