<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute;

use Ghostwriter\Container\Attribute\Extension;
use Tests\Fixture\ClientInterface;
use Tests\Fixture\Extension\ClassHasExtensionAttributeExtension;

#[Extension(ClassHasExtensionAttributeExtension::class)]
final readonly class ClassHasOneExtensionAttribute
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
