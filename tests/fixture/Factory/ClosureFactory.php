<?php

declare(strict_types=1);

namespace Tests\Fixture\Factory;

use Closure;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Throwable;

final readonly class ClosureFactory implements FactoryInterface
{
    public function __construct(
        private Closure $closure,
    ) {}

    public static function new(Closure $closure): self
    {
        return new self($closure);
    }

    /**
     * @throws Throwable
     */
    public function __invoke(ContainerInterface $container): object
    {
        return $container->call($this->closure);
    }
}
