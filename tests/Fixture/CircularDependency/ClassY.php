<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\CircularDependency;

class ClassY
{
    public function __construct(ClassZ $classZ)
    {
    }
}
