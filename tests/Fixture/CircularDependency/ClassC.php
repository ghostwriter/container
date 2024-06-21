<?php

declare(strict_types=1);

namespace Tests\Fixture\CircularDependency;

final class ClassC
{
    public function __construct(ClassX $classX)
    {
    }
}
