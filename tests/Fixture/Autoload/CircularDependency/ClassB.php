<?php

declare(strict_types=1);

namespace Tests\Fixture\CircularDependency;

final class ClassB
{
    public function __construct(ClassC $classC)
    {
    }
}
