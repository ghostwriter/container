<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\Exception\BuilderAlreadyExistsException;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\Name\Service;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Service::class)]
#[CoversClass(Builders::class)]
final class BuildersTest extends TestCase
{
    public function testThrowsBuilderAlreadyExistsException(): void
    {
        $this->expectException(BuilderAlreadyExistsException::class);

        $builders = Builders::new();

        $builders->set(stdClass::class, static fn (): stdClass => new stdClass());

        $builders->set(stdClass::class, static fn (): stdClass => new stdClass());
    }
}
