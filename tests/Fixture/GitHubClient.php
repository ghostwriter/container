<?php

declare(strict_types=1);

namespace Tests\Fixture;

final class GitHubClient implements ClientInterface
{
    public function token(): string
    {
        return sprintf('%s%s', 'ghp_', str_repeat('a', 36));
    }
}
