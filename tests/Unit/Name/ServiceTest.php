<?php

declare(strict_types=1);

namespace Tests\Unit\Name;

use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Service::class)]
final class ServiceTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
