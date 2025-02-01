<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Attribute;

use Attribute;
use Ghostwriter\Container\Interface\Attribute\ExtensionAttributeInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Override;

/**
 * @template-covariant TService of object
 *
 * @implements ExtensionAttributeInterface<ExtensionInterface<TService>>
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final readonly class Extension implements ExtensionAttributeInterface
{
    /**
     * @param class-string<ExtensionInterface<TService>> $service
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
