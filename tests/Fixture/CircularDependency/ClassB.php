<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\CircularDependency;

class ClassB
{
    public function __construct(ClassC $classC)
    {
    }
}
