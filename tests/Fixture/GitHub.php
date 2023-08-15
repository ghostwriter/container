<?php

namespace Ghostwriter\Container\Tests\Fixture;

final class GitHub
{
    public function __construct(
        private readonly ClientInterface $client
    ) {
    }
    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}