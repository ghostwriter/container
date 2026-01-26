<?php

declare(strict_types=1);

namespace Tests\Fixture\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Override;
use Tests\Fixture\Attribute\Extension\ClassRequiresExtensionAttribute;
use Tests\Fixture\Foobar;
use Throwable;

/**
 * @implements ExtensionInterface<ClassRequiresExtensionAttribute>
 */
final readonly class ClassRequiresExtensionAttributeExtension implements ExtensionInterface
{
    /**
     * @param ClassRequiresExtensionAttribute $service
     *
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container, object $service): void
    {
        $service->setFoobar($container->get(Foobar::class));
    }
}
