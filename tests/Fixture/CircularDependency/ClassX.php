<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\CircularDependency;

class ClassX
{
    public function __construct(ClassY $classY)
    {
    }
}
