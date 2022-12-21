<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

class Dummy implements DummyInterface
{
    public function __construct()
    {
    }

    public function __invoke(
        array $data = [],
        string $text = 'Untitled',
    ): string {
        return sprintf($text, ...$data);
    }
}
