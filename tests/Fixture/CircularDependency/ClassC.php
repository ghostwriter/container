<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\CircularDependency;

class ClassC
{
    public function __construct(ClassX $classX)
    {
    }
}
