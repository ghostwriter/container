<?php

declare(strict_types=1);

namespace Ghostwriter\Container;

use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Interface\ContainerInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

use function array_key_exists;
use function array_reduce;
use function is_a;
use function is_callable;
use function sprintf;

/** @see \Ghostwriter\ContainerTests\Unit\ParameterBuilderTest */
final readonly class ParameterBuilder
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    /**
     * @template TParameter
     *
     * @param array<ReflectionParameter> $reflectionParameters
     * @param array<TParameter>          $arguments
     *
     * @throws Throwable
     *
     * @return array<TParameter>
     */
    public function build(array $reflectionParameters = [], array $arguments = []): array
    {
        $container = $this->container;

        /** @var array<TParameter> */
        return array_reduce(
            $reflectionParameters,
            /** @throws Throwable */
            static function (
                array $parameters,
                ReflectionParameter $reflectionParameter
            ) use ($container, &$arguments): array {
                $parameterName = $reflectionParameter->getName();
                $parameterPosition = $reflectionParameter->getPosition();

                if ($arguments !== []) {
                    if (array_key_exists($parameterName, $arguments)) {
                        /** @var TParameter $argument */
                        $argument = $arguments[$parameterName];

                        unset($arguments[$parameterName]);

                        $parameters[$parameterPosition] = $argument;

                        return $parameters;
                    }

                    if (array_key_exists($parameterPosition, $arguments)) {
                        /** @var TParameter $argument */
                        $argument = $arguments[$parameterPosition];

                        unset($arguments[$parameterPosition]);

                        $parameters[$parameterPosition] = $argument;

                        return $parameters;
                    }
                }

                $isDefaultValueAvailable = $reflectionParameter->isDefaultValueAvailable();
                $reflectionType = $reflectionParameter->getType();
                if ($reflectionType instanceof ReflectionNamedType && ! $reflectionType->isBuiltin()) {
                    $reflectionTypeName = $reflectionType->getName();

                    /** @var TParameter $parameters */
                    $parameters[$parameterPosition] = match (true) {
                        default => $container->get($reflectionTypeName),
                        $isDefaultValueAvailable => match (true) {
                            is_a($reflectionTypeName, ContainerInterface::class, true) => $container,
                            $container->has($reflectionTypeName) => $container->get($reflectionTypeName),
                            default => $reflectionParameter->getDefaultValue(),
                        }
                    };

                    return $parameters;
                }

                if (! $isDefaultValueAvailable) {
                    $name = $reflectionParameter->getDeclaringFunction()
                        ->getName();

                    $isFunction = is_callable($name);

                    throw new UnresolvableParameterException(sprintf(
                        'Unresolvable %s parameter "$%s" in "%s%s()"; does not have a default value.',
                        $isFunction ? 'function' : 'class',
                        $parameterName,
                        $isFunction ? $name : $reflectionParameter->getDeclaringClass()?->getName(),
                        $isFunction ? '' : '::' . $name
                    ));
                }

                /** @var TParameter $parameters */
                $parameters[$parameterPosition] = $reflectionParameter->getDefaultValue();

                return $parameters;
            },
            []
        );
    }

    public static function new(ContainerInterface $container): self
    {
        return new self($container);
    }
}
