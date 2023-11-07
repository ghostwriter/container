<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Ghostwriter\Container\Alias;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\ServiceMustBeNonEmptyStringException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Alias::class)]
final class AliasTest extends AbstractTestCase
{
    public function testAlias(): void
    {
        $aliasName = 'class';
        $serviceName = stdClass::class;

        $factory = new Alias($aliasName, $serviceName);

        $this->assertSame($aliasName, $factory->name());
        $this->assertSame($serviceName, $factory->service());
    }

    public function testAliasThrowsAliasNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(AliasNameMustBeNonEmptyStringException::class);

        $aliasName = '';
        $serviceName = stdClass::class;

        new Alias($aliasName, $serviceName);
    }
    public function testAliasThrowsServiceMustBeNonEmptyStringException(): void
    {
        $this->expectException(ServiceMustBeNonEmptyStringException::class);

        $aliasName = stdClass::class;
        $serviceName = '';

        new Alias($aliasName, $serviceName);
    }
    public function testAliasThrowsAliasNameAndServiceNameCannotBeTheSameException(): void
    {
        $this->expectException(AliasNameAndServiceNameCannotBeTheSameException::class);

        $aliasName = stdClass::class;
        $serviceName = stdClass::class;

        new Alias($aliasName, $serviceName);
    }
}
