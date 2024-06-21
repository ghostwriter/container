<?php

declare(strict_types=1);

namespace Tests\Fixture\CircularDependency;

final class ClassZ
{
    public function __construct(ClassA $classA)
    {
    }
}
