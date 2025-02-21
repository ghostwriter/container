<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Attribute;

use Attribute;
use Ghostwriter\Container\Interface\AttributeInterface;
use Override;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Inject implements AttributeInterface
{
    /**
     * @param class-string $name
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
