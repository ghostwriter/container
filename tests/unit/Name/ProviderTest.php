<?php

declare(strict_types=1);

namespace Tests\Unit\Name;

use Ghostwriter\Container\Name\Provider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Provider::class)]
final class ProviderTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
