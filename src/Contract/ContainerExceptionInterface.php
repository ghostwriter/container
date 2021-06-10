<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Contract;

use Psr\Container\ContainerExceptionInterface as PsrContainerExceptionInterface;
use Throwable;

interface ContainerExceptionInterface extends Throwable, PsrContainerExceptionInterface
{
}
