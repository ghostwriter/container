<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Ghostwriter\Container\Attribute\Inject;

final readonly class GitHub
{
    public function __construct(
        #[Inject(GitHubClient::class)]
        private ClientInterface $client
    ) {
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}
