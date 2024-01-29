<?php

declare(strict_types=1);

namespace Ghostwriter\ContainerTests\Fixture;

function typedFunction(TestEvent $event): void
{
    $event->collect(__METHOD__);
}

function typelessFunction($event): void
{
    $event->collect(__METHOD__);
}
