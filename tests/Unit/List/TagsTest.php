<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Tags;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tags::class)]
final class TagsTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
