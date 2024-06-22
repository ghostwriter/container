<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Attribute;

use Attribute;
use Ghostwriter\Container\Exception\ShouldNotHappenException;
use Ghostwriter\Container\Interface\Attribute\InjectAttributeInterface;
use Override;

/**
 * @template TService of object
 * @template TConcrete of object
 *
 * @implements InjectAttributeInterface<TService,TConcrete>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Inject implements InjectAttributeInterface
{
    /**
     * @param class-string<TService>       $service
     * @param null|class-string<TConcrete> $concrete
     */
    public function __construct(
        public string $service,
        public ?string $concrete = null,
    ) {
    }

    /**
     * @return class-string<TConcrete>
     */
    #[Override]
    public function concrete(): string
    {
        return $this->concrete ?? throw new ShouldNotHappenException();
    }

    #[Override]
    public function service(): string
    {
        return $this->service;
    }
}
