<?php

declare(strict_types=1);

namespace Tests\Unit\Name;

use Ghostwriter\Container\Name\Extension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Extension::class)]
final class ExtensionTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
