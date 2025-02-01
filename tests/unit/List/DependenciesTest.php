<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Dependencies;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Dependencies::class)]
final class DependenciesTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
