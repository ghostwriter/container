<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\CircularDependency;

class ClassA
{
    public function __construct(ClassB $classB)
    {
    }
}
