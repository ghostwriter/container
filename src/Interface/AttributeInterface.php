<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

/**
 * @template TService of object
 */
interface AttributeInterface
{
    /**
     * @return class-string<TService>
     */
    public function service(): string;
}
