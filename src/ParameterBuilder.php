<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Interface\ContainerInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;
use function array_key_exists;
use function array_key_first;
use function is_callable;

/** @see Ghostwriter\Container\Tests\Unit\ParameterBuilderTest */
final readonly class ParameterBuilder
{
    /**
     * @template TParameter
     *
     * @param array<ReflectionParameter> $reflectionParameters
     * @param array<TParameter> $arguments
     *
     * @return array<TParameter>
     * @throws Throwable
     */
    public function build(
        ContainerInterface $container,
        array              $reflectionParameters = [],
        array              $arguments = []
    ): array {
        /** @var array<TParameter> */
        return array_map(
            /** @throws Throwable */
            static function (ReflectionParameter $reflectionParameter) use ($container, &$arguments): mixed {
                $parameterName = $reflectionParameter->getName();

                if ($arguments !== []) {
                    /** @var class-string<TParameter> $parameterKey */
                    $parameterKey = array_key_exists($parameterName, $arguments) ?
                        $parameterName :
                        array_key_first($arguments);

                    /** @var TParameter $argument */
                    $argument = $arguments[$parameterKey];

                    unset($arguments[$parameterKey]);

                    return $argument;
                }

                $isDefaultValueAvailable = $reflectionParameter->isDefaultValueAvailable();

                $reflectionType = $reflectionParameter->getType();

                if ($reflectionType instanceof ReflectionNamedType && !$reflectionType->isBuiltin()) {
                    $reflectionTypeName = $reflectionType->getName();

                    /** @var TParameter */
                    return match (true) {
                        default => $container->get($reflectionTypeName),
                        $isDefaultValueAvailable => match (true) {
                            $container->has($reflectionTypeName) => $container->get($reflectionTypeName),
                            default => $reflectionParameter->getDefaultValue(),
                        }
                    };
                }

                if ($isDefaultValueAvailable) {
                    /** @var TParameter */
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
            $reflectionParameters
        );
    }
}
