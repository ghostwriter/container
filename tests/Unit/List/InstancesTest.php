<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Instances;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Instances::class)]
final class InstancesTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
