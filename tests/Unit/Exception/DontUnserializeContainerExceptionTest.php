<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;
use function mb_strlen;
use function sprintf;
use function unserialize;

#[CoversClass(DontUnserializeContainerException::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]

#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
final class DontUnserializeContainerExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testUnserialize(): void
    {
        $this->assertException(DontUnserializeContainerException::class);

        unserialize(
            // mocks a serialized Container::class
            sprintf('O:%s:"%s":0:{}', mb_strlen(Container::class), Container::class)
        );
    }
}
