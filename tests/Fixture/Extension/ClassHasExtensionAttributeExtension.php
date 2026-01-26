<?php

declare(strict_types=1);

namespace Tests\Fixture\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Override;
use Tests\Fixture\Attribute\Extension\ClassHasExtensionAttribute;
use Tests\Fixture\Foobar;
use Throwable;
use function time;

/**
 * @implements ExtensionInterface<ClassHasExtensionAttribute>
 */
final readonly class ClassHasExtensionAttributeExtension implements ExtensionInterface
{
    /**
     * @param ClassHasExtensionAttribute $service
     *
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container, object $service): void
    {
        $service->setFoobar(new Foobar(time()));
    }
}
