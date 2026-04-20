<?php

declare(strict_types=1);

namespace Tests\Fixture\CircularDependency;

final class ClassA
{
    public function __construct(ClassB $classB)
    {
    }
}
