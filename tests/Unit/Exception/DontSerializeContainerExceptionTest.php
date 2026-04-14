<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Provider\ComposerDefinitionProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

use function serialize;

#[CoversClass(DontSerializeContainerException::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClass(ContainerProvider::class)]
#[CoversClass(ComposerDefinitionProvider::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]

final class DontSerializeContainerExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testSerialize(): void
    {
        $this->assertException(DontSerializeContainerException::class);

        self::assertNull(serialize($this->container));
    }
}
