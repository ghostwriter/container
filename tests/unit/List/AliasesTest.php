<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Aliases;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Aliases::class)]
final class AliasesTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
