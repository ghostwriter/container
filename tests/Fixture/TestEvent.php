<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture;

class TestEvent
{
    /** @var array<array-key,string> */
    private array $events = [];

    public function collect(string $event): void
    {
        $this->events[] = $event . time();
    }

    /** @return array<array-key,string> */
    public function all(): array
    {
        return $this->events;
    }
}
