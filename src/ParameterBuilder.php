<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Interface\ExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;
use function array_key_exists;
use function array_key_first;
use function array_values;
use function is_callable;

final readonly class ParameterBuilder
{
    /**
     * @template TArgument
     *
     * @param array<ReflectionParameter> $parameters
     * @param array<TArgument> $arguments
     *
     * @return array<TArgument>
     * @throws ReflectionException
     * @throws Throwable
     * @throws ExceptionInterface
     *
     * @throws NotFoundExceptionInterface
     */
    public function build(
        ContainerInterface $container,
        array              $parameters = [],
        array              $arguments = []
    ): array
    {
        /** @var array<TArgument> */
        return [
            ...array_map(
            /** @throws Throwable */
                static function (ReflectionParameter $reflectionParameter) use ($container, &$arguments) {
                    $parameterName = $reflectionParameter->getName();
                    if ($arguments !== []) {
                        /** @var class-string<TArgument> $parameterKey */
                        $parameterKey = array_key_exists($parameterName, $arguments) ?
                            $parameterName :
                            array_key_first($arguments);

                        /** @var TArgument $argument */
                        $argument = $arguments[$parameterKey];

                        unset($arguments[$parameterKey]);

                        return $argument;
                    }

                    $reflectionType = $reflectionParameter->getType();
                    if ($reflectionType instanceof ReflectionNamedType && !$reflectionType->isBuiltin()) {
                        /** @var TArgument */
                        return $container->get($reflectionType->getName());
                    }

                    if ($reflectionParameter->isDefaultValueAvailable()) {
                        /** @var TArgument */
                        return $reflectionParameter->getDefaultValue();
                    }

                    $name = $reflectionParameter->getDeclaringFunction()->getName();

                    $isFunction = is_callable($name);

                    throw new UnresolvableParameterException(sprintf(
                        'Unresolvable %s parameter "$%s" in "%s%s()"; does not have a default value.',
                        $isFunction ? 'function' : 'class',
                        $parameterName,
                        $isFunction ? $name : $reflectionParameter->getDeclaringClass()?->getName(),
                        $isFunction ? '' : '::' . $name
                    ));
                },
                $parameters
            ),
            ...array_values($arguments)
        ];
    }
}
