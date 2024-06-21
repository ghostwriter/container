<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute;

use Ghostwriter\Container\Attribute\Inject;
use Tests\Fixture\ClientInterface;
use Tests\Fixture\GitHubClient;

final readonly class ClassParameterHasInjectAttribute
{
    public function __construct(
        #[Inject(GitHubClient::class, self::class)]
        private ClientInterface $client
    ) {
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}
