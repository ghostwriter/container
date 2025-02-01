<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Ghostwriter\Container\Attribute\Factory;
use Tests\Fixture\Factory\DummyFactory;

use function array_values;
use function sprintf;

#[Factory(DummyFactory::class)]
final readonly class Dummy implements DummyInterface
{
    public function __construct(
        private DummyFactory $dummyFactory
    ) {
    }

    /**
     * @template T of string
     * @param array<T> $data
     */
    public function __invoke(array $data = ['Text'], string $text = 'Untitled %s'): string
    {
        return sprintf($text, ...array_values($data));
    }

    public function getDummyFactory(): DummyFactory
    {
        return $this->dummyFactory;
    }
}
