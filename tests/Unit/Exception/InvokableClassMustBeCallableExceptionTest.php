<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\InvokableClassMustBeCallableException;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvokableClassMustBeCallableException::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]

final class InvokableClassMustBeCallableExceptionTest extends TestCase
{
    public function testThrowsInvokableClassMustBeCallableException(): void
    {
        $this->expectException(InvokableClassMustBeCallableException::class);

        $container = Container::getInstance();

        $container->call(InvokableClassMustBeCallableException::class);
    }
}
