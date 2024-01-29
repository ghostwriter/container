<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\CircularDependency;

class ClassX
{
    public function __construct(ClassY $classY)
    {
    }
}
