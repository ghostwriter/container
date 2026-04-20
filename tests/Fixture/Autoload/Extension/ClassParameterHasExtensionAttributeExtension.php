<?php

declare(strict_types=1);

namespace Tests\Fixture\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Override;
use Tests\Fixture\Attribute\Extension\ClassParameterHasExtensionAttribute;
use Tests\Fixture\Foobar;

/**
 * @implements ExtensionInterface<ClassParameterHasExtensionAttribute>
 */
final readonly class ClassParameterHasExtensionAttributeExtension implements ExtensionInterface
{
    /**
     * @param ClassParameterHasExtensionAttribute $service
     */
    #[Override] public function __invoke(ContainerInterface $container, object $service): void
    {
        $service->setFoobar(new Foobar(time()));
    }
}
