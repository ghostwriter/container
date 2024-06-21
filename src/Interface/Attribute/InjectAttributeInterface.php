<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Attribute;

use Ghostwriter\Container\Interface\AttributeInterface;

/**
 * @template TService of object
 * @template TConcrete of object
 *
 * @extends AttributeInterface<TService>
 */
interface InjectAttributeInterface extends AttributeInterface
{
    /**
     * @return class-string<TConcrete>
     */
    public function concrete(): string;
}
