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
     * @param class-string<ServiceProviderInterface> $class
     */
    public function __construct(
        public string $class,
    ) {}

    /**
     * @return class-string<ServiceProviderInterface>
     */
    #[Override]
    public function service(): string
    {
        return $this->class;
    }
}
