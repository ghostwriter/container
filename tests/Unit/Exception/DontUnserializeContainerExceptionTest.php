<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Service\Provider\ComposerServiceProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

use function mb_strlen;
use function sprintf;
use function unserialize;

#[CoversClass(DontUnserializeContainerException::class)]
#[CoversClass(Container::class)]
#[CoversClass(ContainerProvider::class)]
#[CoversClass(ComposerServiceProvider::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
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
