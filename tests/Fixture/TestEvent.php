<?php

declare(strict_types=1);

namespace Tests\Fixture;

use function time;

final class TestEvent
{
    /** @var array<array-key,string> */
    private array $events = [];

    /** @return array<array-key,string> */
    public function all(): array
    {
        return $this->events;
    }

    public function collect(string $event): void
    {
        $this->events[] = $event . time();
    }
}
