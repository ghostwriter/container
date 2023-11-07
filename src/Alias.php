<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\ServiceMustBeNonEmptyStringException;

final readonly class Alias
{
    public function __construct(
        private string $name,
        private string $service,
    ) {
        if ('' === trim($name)) {
            throw new AliasNameMustBeNonEmptyStringException();
        }

        if ('' === trim($service)) {
            throw new ServiceMustBeNonEmptyStringException();
        }

        if ($name === $service) {
            throw new AliasNameAndServiceNameCannotBeTheSameException($name);
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function service(): string
    {
        return $this->service;
    }
}
