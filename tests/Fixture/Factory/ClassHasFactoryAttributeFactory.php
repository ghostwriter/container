<?php

declare(strict_types=1);

namespace Tests\Fixture\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;
use Tests\Fixture\Attribute\ClassHasFactoryAttribute;
use Tests\Fixture\Foobar;
use Throwable;

use function time;

/**
 * @implements FactoryInterface<ClassHasFactoryAttribute>
 */
final readonly class ClassHasFactoryAttributeFactory implements FactoryInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): ClassHasFactoryAttribute
    {
        return new ClassHasFactoryAttribute(new Foobar(time()));
    }
}
