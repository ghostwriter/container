<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture;

class UnresolvableParameter
{
    private mixed $number;

    public function __construct(mixed $number)
    {
        $this->number = $number;
    }

    public function value(): mixed
    {
        return $this->number;
    }
}
