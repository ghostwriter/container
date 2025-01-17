<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Factories;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Factories::class)]
final class FactoriesTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
