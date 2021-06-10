<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Contract\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface as PsrNotFoundExceptionInterface;

interface NotFoundExceptionInterface extends ContainerExceptionInterface, PsrNotFoundExceptionInterface
{
}
