<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

class TestEvent
{
    /** @var array<array-key,string> */
    private array $events = [];

    public function collect(string $event): void
    {
        $this->events[] = $event;
    }

    /** @return array<array-key,string> */
    public function all(): array
    {
        return $this->events;
    }
}
