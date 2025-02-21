<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Attribute;

use Attribute;
use Ghostwriter\Container\Interface\AttributeInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Override;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Extension implements AttributeInterface
{
    /**
     * @param class-string<ExtensionInterface> $name
     */
    public function __construct(
        public string $name,
    ) {}

    /**
     * @return class-string
     */
    #[Override]
    public function name(): string
    {
        return $this->name;
    }
}
