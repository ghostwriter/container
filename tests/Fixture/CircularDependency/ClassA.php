<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\CircularDependency;

class ClassA
{
    public function __construct(ClassB $classB)
    {
    }
}
