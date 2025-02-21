<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

interface AttributeInterface
{
    /**
     * @return class-string
     */
    public function name(): string;
}
