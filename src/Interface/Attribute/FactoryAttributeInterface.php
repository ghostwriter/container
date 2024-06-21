<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Attribute;

use Ghostwriter\Container\Interface\AttributeInterface;
use Ghostwriter\Container\Interface\FactoryInterface;

/**
 * @template TService of object
 *
 * @extends AttributeInterface<FactoryInterface<TService>>
 */
interface FactoryAttributeInterface extends AttributeInterface
{
}
