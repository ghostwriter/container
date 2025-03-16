<?php

declare(strict_types=1);

namespace Tests\Fixture\Attribute\Extension;

use Ghostwriter\Container\Attribute\Extension;
use Tests\Fixture\ClientInterface;
use Tests\Fixture\Extension\ClassHasExtensionAttributeExtension;

#[Extension(ClassHasExtensionAttributeExtension::class)]
#[Extension(ClassHasExtensionAttributeExtension::class)]
final readonly class ClassHasManyExtensionAttribute
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
