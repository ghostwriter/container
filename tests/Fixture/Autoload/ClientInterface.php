<?php

declare(strict_types=1);

namespace Tests\Fixture;

interface ClientInterface
{
    public function token(): string;
}
