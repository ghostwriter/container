<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Interface\ContainerInterface;
use Override;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface as PsrNotFoundExceptionInterface;
use RuntimeException;
use Throwable;

final readonly class PsrContainer implements PsrContainerInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public static function new(?ContainerInterface $container = null): self
    {
        return new self($container ?? Container::getInstance());
    }

    /**
     * @template TObject of object
     *
     * @param class-string<TObject> $id
     *
     * @throws Throwable
     *
     * @return TObject
     */
    #[Override]
    public function get(string $id): mixed
    {
        try {
            return $this->container->get($id);
        } catch (Throwable $e) {
            throw new class(
                $e::class . ': ' . $e->getMessage(),
                $e->getCode(),
                $e
            ) extends RuntimeException implements PsrNotFoundExceptionInterface {};
        }
    }

    #[Override]
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
