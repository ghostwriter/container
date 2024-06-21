<?php

declare(strict_types=1);

namespace Tests\Fixture\CircularDependency;

final class ClassY
{
    public function __construct(ClassZ $classZ)
    {
    }
}
