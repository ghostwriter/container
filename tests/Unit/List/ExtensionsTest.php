<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Extensions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Extensions::class)]
final class ExtensionsTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
