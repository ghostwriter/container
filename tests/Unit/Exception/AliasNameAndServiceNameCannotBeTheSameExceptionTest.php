<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use Ghostwriter\Container\Name\Alias;
use Ghostwriter\Container\Name\Service;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use stdClass;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(AliasNameAndServiceNameCannotBeTheSameException::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
final class AliasNameAndServiceNameCannotBeTheSameExceptionTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        $this->assertException(AliasNameAndServiceNameCannotBeTheSameException::class);

        $this->container
            ->alias(stdClass::class, stdClass::class)
        ;
    }
}
