<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Attribute;

use Attribute;
use Ghostwriter\Container\Interface\AttributeInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Override;

/**
 * @implements AttributeInterface<ServiceProviderInterface>
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Provider implements AttributeInterface
{
    /**
     * @param class-string<ServiceProviderInterface> $name
     */
    public function __construct(
        public string $name,
    ) {}

    /**
     * @return class-string<ServiceProviderInterface>
     */
    #[Override]
    public function name(): string
    {
        return $this->name;
    }
}
