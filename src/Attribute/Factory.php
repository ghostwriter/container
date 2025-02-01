<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Attribute;

use Attribute;
use Ghostwriter\Container\Interface\Attribute\FactoryAttributeInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;

/**
 * @template-covariant TService of object
 *
 * @implements FactoryAttributeInterface<FactoryInterface<TService>>
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER)]
final readonly class Factory implements FactoryAttributeInterface
{
    /**
     * @param class-string<FactoryInterface<TService>> $service
     */
    public function __construct(
        public string $service,
    ) {}

    #[Override]
    public function service(): string
    {
        return $this->service;
    }
}
