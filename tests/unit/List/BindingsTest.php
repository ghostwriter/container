<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\Name\Alias;
use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Service::class)]
#[CoversClass(Bindings::class)]
final class BindingsTest extends TestCase
{
    public function testContains(): void
    {
        $bindings = Bindings::new();

        self::assertFalse($bindings->contains(ContainerInterface::class));

        $bindings->set(Alias::class, Alias::class, Container::class);
        $bindings->set(Service::class, ContainerInterface::class, Container::class);

        self::assertTrue($bindings->contains(ContainerInterface::class));
    }
}
