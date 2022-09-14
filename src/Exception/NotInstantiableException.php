<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Exception;

use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use RuntimeException as PHPRuntimeException;
use function sprintf;

final class NotInstantiableException extends PHPRuntimeException implements ContainerExceptionInterface
{
    /**
     * @param class-string $id
     */
    public static function abstractClassOrInterface(string $id): self
    {
        return new self(sprintf('Class "%s" is an abstract class or interface; not instantiable.', $id));
    }

    /**
     * @param class-string $class
     */
    public static function classDoseNotExist(string $class): self
    {
        return new self(sprintf('Class "%s" dose not exist.', $class));
    }

    /**
     * @param class-string $class
     */
    public static function unresolvableParameter(string $parameter, string $class, string $method): self
    {
        $isFunction = '' === $class;

        return new self(sprintf(
            'Unresolvable %s parameter "$%s" in "%s%s"; does not have a default value.',
            $isFunction ? 'function' : 'class',
            $parameter,
            $isFunction ? $method : $class,
            $isFunction ? '()' : '::' . $method
        ));
    }
}
