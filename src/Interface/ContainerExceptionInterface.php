<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface;

use Psr\Container\ContainerExceptionInterface as PsrContainerExceptionInterface;
use Throwable;

interface ContainerExceptionInterface extends PsrContainerExceptionInterface, Throwable {}
