<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

final readonly class ParameterBuilder
{
    /**
     * @template TArgument
     *
     * @param array<ReflectionParameter> $parameters
     * @param array<TArgument>           $arguments
     *
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws Throwable
     * @throws ContainerExceptionInterface
     *
     * @return array<TArgument>
     */
    public function build(
        ContainerInterface $container,
        array $parameters = [],
        array $arguments = []
    ): array {
        return [...array_map(
            /**
             * @throws ContainerExceptionInterface
             * @throws NotFoundExceptionInterface
             * @throws ReflectionException
             * @throws Throwable
             */
            static function (ReflectionParameter $reflectionParameter) use ($container, &$arguments) {
                $parameterName = $reflectionParameter->getName();
                if ($arguments !== []) {
                    $parameterKey = array_key_exists($parameterName, $arguments) ?
                        $parameterName :
                        array_key_first($arguments);

                    $argument = $arguments[$parameterKey];

                    unset($arguments[$parameterKey]);

                    return $argument;
                }

                $reflectionType = $reflectionParameter->getType();
                if ($reflectionType instanceof ReflectionNamedType && ! $reflectionType->isBuiltin()) {
                    return $container->get($reflectionType->getName());
                }

                if ($reflectionParameter->isDefaultValueAvailable()) {
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
            ...array_values($arguments)];
    }
}
