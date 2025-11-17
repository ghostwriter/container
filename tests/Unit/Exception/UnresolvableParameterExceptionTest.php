<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Fixture\UnresolvableParameter;
use Tests\Unit\AbstractTestCase;
use Throwable;

use function sprintf;

#[CoversClass(UnresolvableParameterException::class)]
#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]

final class UnresolvableParameterExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testContainerBuild(): void
    {
        $this->assertException(UnresolvableParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable class parameter "$number" in "%s::%s"; does not have a default value.',
            UnresolvableParameter::class,
            '__construct()'
        ));

        $this->container->build(UnresolvableParameter::class);
    }

    /** @throws Throwable */
    public function testContainerCall(): void
    {
        $this->assertException(UnresolvableParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable function parameter "%s" in "%s"; does not have a default value.',
            '$event',
            'Tests\Fixture\typelessFunction()',
        ));

        $this->container->call('Tests\Fixture\typelessFunction');
    }
}
