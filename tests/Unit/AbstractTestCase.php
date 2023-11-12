<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Ghostwriter\Container\Container;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @throws Throwable
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Container::getInstance()->__destruct();
    }
}
