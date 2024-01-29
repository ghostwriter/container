<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture\CircularDependency;

class ClassZ
{
    public function __construct(ClassA $classA)
    {
    }
}
