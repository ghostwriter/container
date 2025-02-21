<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\Exception\ShouldNotHappenException;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Service::class)]
#[CoversClass(Dependencies::class)]
final class DependenciesTest extends TestCase
{
    public function testLast(): void
    {
        $dependencies = Dependencies::new();

        $dependencies->set(stdClass::class);

        self::assertSame(stdClass::class, $dependencies->last());
    }

    public function testLastThrows(): void
    {
        $dependencies = Dependencies::new();

        $this->expectException(ShouldNotHappenException::class);

        $dependencies->last();
    }

    public function testMissing(): void
    {
        $dependencies = Dependencies::new();

        self::assertFalse($dependencies->missing());

        $dependencies->set(stdClass::class);

        self::assertTrue($dependencies->missing());
    }
}
