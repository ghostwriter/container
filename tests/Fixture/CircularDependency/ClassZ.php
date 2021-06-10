<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture\CircularDependency;

class ClassZ
{
    public function __construct(ClassA $classA)
    {
    }
}
