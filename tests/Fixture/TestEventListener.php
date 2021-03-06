<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;


class TestEventListener
{
    public function __invoke(TestEvent $event): void
    {
        $event->collect(__METHOD__);
    }

    public function onTest(TestEvent $event): void
    {
        $event->collect(__METHOD__);
    }

    public static function onStatic(TestEvent $event, string $nullableWithDefault = null): void
    {
        $event->collect(__METHOD__);
    }

    public static function onStaticCallableArray(TestEvent $event, ?string $nullable): void
    {
        $event->collect(__METHOD__);
    }
}
