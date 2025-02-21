<?php

declare(strict_types=1);

namespace Tests\Unit\List;

use Ghostwriter\Container\List\Tags;
use Ghostwriter\Container\Name\Service;
use Ghostwriter\Container\Name\Tag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Service::class)]
#[CoversClass(Tag::class)]
#[CoversClass(Tags::class)]
final class TagsTest extends TestCase
{
    public function testExample(): void
    {
        $tags = Tags::new();

        self::assertFalse($tags->has('example'));

        $tags->set(self::class, ['self']);
        $tags->set(stdClass::class, ['example']);

        self::assertTrue($tags->has('example'));

        $tags->unset(stdClass::class);

        self::assertFalse($tags->has('example'));
    }
}
