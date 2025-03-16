<?php

declare(strict_types=1);

namespace Tests\Unit\Name;

use Ghostwriter\Container\Name\Tag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tag::class)]
final class TagTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
