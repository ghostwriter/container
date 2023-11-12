<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

final readonly class GitHub
{
    public function __construct(
        private ClientInterface $client
    ) {
    }
    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}
