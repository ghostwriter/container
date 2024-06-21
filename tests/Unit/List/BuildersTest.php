<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Builders;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Builders::class)]
final class BuildersTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
