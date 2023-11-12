<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Fixture;

final readonly class Dummy implements DummyInterface
{
    public function __construct(private DummyFactory $dummyFactory)
    {
    }

    public function __invoke(
        array $data = ['Text'],
        string $text = 'Untitled %s',
    ): string {
        return sprintf($text, ...array_values($data));
    }

    /**
     * @return DummyFactory
     */
    public function getDummyFactory(): DummyFactory
    {
        return $this->dummyFactory;
    }
}
