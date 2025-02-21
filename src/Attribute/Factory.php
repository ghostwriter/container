<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Attribute;

use Attribute;
use Ghostwriter\Container\Interface\AttributeInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Factory implements AttributeInterface
{
    /**
     * @param class-string<FactoryInterface> $name
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
