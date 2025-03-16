<?php

declare(strict_types=1);

namespace Tests\Unit\Name;

use Ghostwriter\Container\Name\Alias;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Alias::class)]
final class AliasTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
