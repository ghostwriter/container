<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Providers;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Providers::class)]
final class ProvidersTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
