<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Provider\ComposerDefinitionProvider;
use Ghostwriter\Container\Service\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use stdClass;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(AliasNameAndServiceNameCannotBeTheSameException::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClass(ContainerProvider::class)]
#[CoversClass(ComposerDefinitionProvider::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
final class AliasNameAndServiceNameCannotBeTheSameExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testContainerAlias(): void
    {
        $this->assertException(AliasNameAndServiceNameCannotBeTheSameException::class);

        $this->container
            ->alias(stdClass::class, stdClass::class)
        ;
    }
}
