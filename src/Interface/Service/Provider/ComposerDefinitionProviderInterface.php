<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Service\Provider;

use Ghostwriter\Container\Interface\Service\ProviderInterface;

interface ComposerDefinitionProviderInterface extends ProviderInterface
{
    /** @param class-string<ProviderInterface> $provider */
    public function add(string $provider): void;
}
