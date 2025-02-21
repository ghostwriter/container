<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Error;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNotFoundException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\Name\Alias;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Alias::class)]
#[CoversClass(Aliases::class)]
final class AliasesTest extends TestCase
{
    public function testGet(): void
    {
        $aliases = Aliases::new();

        self::assertSame(Container::class, $aliases->get(ContainerInterface::class));
    }

    public function testGetError(): void
    {
        $aliases = Aliases::new();

        $this->expectException(AliasNotFoundException::class);

        $aliases->get(Error::class);
    }
}
