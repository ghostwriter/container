<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\InvokableClassMustBeCallableException;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Container::class)]
#[CoversClass(Aliases::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Service::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Tags::class)]
#[CoversClass(InvokableClassMustBeCallableException::class)]
final class InvokableClassMustBeCallableExceptionTest extends TestCase
{
    public function testThrowsInvokableClassMustBeCallableException(): void
    {
        $this->expectException(InvokableClassMustBeCallableException::class);

        $container = Container::getInstance();

        $container->invoke(InvokableClassMustBeCallableException::class);
    }
}
