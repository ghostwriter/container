<?php

declare(strict_types=1);

namespace Tests\Unit\Name;

use Ghostwriter\Container\Name\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Factory::class)]
final class FactoryTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
