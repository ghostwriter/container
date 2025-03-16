<?php

declare(strict_types=1);

namespace Tests\Fixture\CircularDependency;

final class ClassX
{
    public function __construct(ClassY $classY)
    {
    }
}
