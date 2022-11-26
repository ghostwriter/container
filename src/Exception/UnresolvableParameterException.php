<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use RuntimeException;

final class UnresolvableParameterException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $parameterName, string $class, string $name)
    {
        $isFunction = '' === $class;
        parent::__construct(
            sprintf(
                'Unresolvable %s parameter "$%s" in "%s%s"; does not have a default value.',
                $isFunction ? 'function' : 'class',
                $parameterName,
                $isFunction ? $name : $class,
                $isFunction ? '()' : '::' . $name
            )
        );
    }
}
