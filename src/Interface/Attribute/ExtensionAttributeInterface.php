<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Attribute;

use Ghostwriter\Container\Interface\AttributeInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;

/**
 * @template TService of object
 *
 * @extends AttributeInterface<ExtensionInterface<TService>>
 */
interface ExtensionAttributeInterface extends AttributeInterface
{
}
