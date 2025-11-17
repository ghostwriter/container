<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Interface\Exception;

use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface as PsrNotFoundExceptionInterface;

interface NotFoundExceptionInterface extends ContainerExceptionInterface, PsrNotFoundExceptionInterface {}
