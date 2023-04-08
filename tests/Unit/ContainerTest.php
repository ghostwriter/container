<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Closure;
use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Contract\ServiceProviderInterface;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\ReflectorException;
use Ghostwriter\Container\Tests\Fixture\Bar;
use Ghostwriter\Container\Tests\Fixture\Baz;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassA;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassB;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassC;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassX;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassY;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassZ;
use Ghostwriter\Container\Tests\Fixture\Constructor\ArrayConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\BoolConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\CallableConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\EmptyConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\FloatConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\IntConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\IterableConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\MixedConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\ObjectConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\OptionalConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\StringConstructor;
use Ghostwriter\Container\Tests\Fixture\Constructor\TypelessConstructor;
use Ghostwriter\Container\Tests\Fixture\Dummy;
use Ghostwriter\Container\Tests\Fixture\DummyInterface;
use Ghostwriter\Container\Tests\Fixture\Extension\FoobarExtension;
use Ghostwriter\Container\Tests\Fixture\Foo;
use Ghostwriter\Container\Tests\Fixture\ServiceProvider\FoobarServiceProvider;
use Ghostwriter\Container\Tests\Fixture\ServiceProvider\FoobarWithDependencyServiceProvider;
use Ghostwriter\Container\Tests\Fixture\TestEvent;
use Ghostwriter\Container\Tests\Fixture\TestEventListener;
use Ghostwriter\Container\Tests\Fixture\UnionTypehintWithDefaultValue;
use Ghostwriter\Container\Tests\Fixture\UnionTypehintWithoutDefaultValue;
use Ghostwriter\Container\Tests\Fixture\UnresolvableParameter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;
use function array_key_exists;
use function is_subclass_of;
use function iterator_to_array;
use function random_int;
use function serialize;
use function sprintf;
use function unserialize;

#[CoversClass(Container::class)]
#[UsesClass(Reflector::class)]
#[Small]
final class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Container::getInstance();
    }

    protected function tearDown(): void
    {
        $this->container->__destruct();
        parent::tearDown();
    }

    /**
     * @return Generator<string,array>
     */
    public static function dataProviderContainerCallables(): Generator
    {
        yield 'AnonymousFunctionCall' => [static function (TestEvent $testEvent): void {
            $testEvent->collect($testEvent::class);
        }];

        yield 'CallableArrayInstanceMethodCall' => [[new TestEventListener(), 'onTest']];
        yield 'CallableArrayInstanceMethodCallOnVariadic' => [[new TestEventListener(), 'onVariadicTest']];
        yield 'CallableArrayStaticMethodCall' => [[TestEventListener::class, 'onStaticCallableArray']];
        yield 'FunctionCall@typedFunction' => ['Ghostwriter\Container\Tests\Fixture\typedFunction'];
        yield 'FunctionCall@typelessFunction' => ['Ghostwriter\Container\Tests\Fixture\typelessFunction'];
        yield 'Invokable' => [new TestEventListener()];
        yield 'StaticMethodCall' => [TestEventListener::class . '::onStatic'];
        yield 'TypelessAnonymousFunctionCall' => [
            static function ($event): void {
                $event->collect($event::class);
            },
        ];
    }

    public static function dataProviderServiceAliasMustBeNonEmptyStringException(): Generator
    {
        yield 'ServiceAliasMustBeNonEmptyStringException@empty-alias' => [
            static fn (Container $container) => $container->alias('empty-alias', ''),
        ];
        yield 'ServiceAliasMustBeNonEmptyStringException@empty-service' => [
            static fn (Container $container) => $container->alias('', 'empty-service'),
        ];
    }

    /**
     * @return Generator<string,array>
     */
    public static function dataProviderServiceAlreadyRegisteredException(): Generator
    {
        yield 'ServiceAlreadyRegisteredException@set' => [
            static fn (Container $container) => $container->set(Container::class, $container),
        ];

        yield 'ServiceAlreadyRegisteredException@set-existing-alias' => [
            static function (Container $container): void {
                $container->alias('container-alias', Container::class);
                $container->set('container-alias', stdClass::class);
            },
        ];

        yield 'ServiceAlreadyRegisteredException@set-existing-factory' => [
            static function (Container $container): void {
                $container->set('container-factory', static fn (): stdClass => new stdClass());
                $container->set('container-factory', $container);
            },
        ];

        yield 'ServiceAlreadyRegisteredException@bind-existing-alias' => [
            static function (Container $container): void {
                $container->set('service', stdClass::class);
                $container->alias('alias', 'service');
                $container->bind('alias', stdClass::class);
            },
        ];

        yield 'ServiceAlreadyRegisteredException@bind-existing-factory' => [
            static function (Container $container): void {
                $container->set(
                    'bind',
                    static fn (Container $container): stdClass => $container->build(stdClass::class)
                );
                $container->bind('bind', stdClass::class);
            },
        ];

        yield 'ServiceAlreadyRegisteredException@bind-existing-service' => [
            static function (Container $container): void {
                $container->set('bind', 'empty-value');
                $container->bind('bind', stdClass::class);
            },
        ];
    }

    /**
     * @return Generator<string,array>
     */
    public static function dataProviderServiceClasses(): Generator
    {
        yield ArrayConstructor::class => [
            ArrayConstructor::class,
            [
                'value' => [],
            ],
        ];

        yield BoolConstructor::class => [
            BoolConstructor::class,
            [
                'value' => true,
            ],
        ];

        yield CallableConstructor::class => [
            CallableConstructor::class,
            [
                'value' => static fn (Container $container) => null,
            ],
        ];

        yield EmptyConstructor::class => [EmptyConstructor::class];
        yield FloatConstructor::class => [
            FloatConstructor::class,
            [
                'value' => 13.37,
            ],
        ];

        yield IntConstructor::class => [
            IntConstructor::class,
            [
                'value' => 42,
            ],
        ];

        yield IterableConstructor::class => [
            IterableConstructor::class,
            [
                'value' => ['iterable'],
            ],
        ];

        yield MixedConstructor::class => [
            MixedConstructor::class,
            [
                'value' => 'mixed',
            ],
        ];

        yield ObjectConstructor::class => [
            ObjectConstructor::class,
            [
                'value' => new stdClass(),
            ],
        ];

        yield OptionalConstructor::class => [OptionalConstructor::class];
        yield StringConstructor::class => [
            StringConstructor::class,
            [
                'value' => 'string',
            ],
        ];

        yield TypelessConstructor::class => [
            TypelessConstructor::class,
            [
                'value' => 'none',
            ],
        ];

        yield UnionTypehintWithoutDefaultValue::class => [
            UnionTypehintWithoutDefaultValue::class,
            [
                'number' => 42,
            ],
        ];

        yield UnionTypehintWithDefaultValue::class => [UnionTypehintWithDefaultValue::class];
        yield Foo::class => [Foo::class];
        yield Bar::class => [Bar::class];
        yield Baz::class => [Baz::class];
        yield Container::class => [Container::class];
        yield FoobarWithDependencyServiceProvider::class => [FoobarWithDependencyServiceProvider::class];
        yield FoobarServiceProvider::class => [FoobarServiceProvider::class];
        yield FoobarExtension::class => [FoobarExtension::class];
        yield self::class => [self::class, ['name']];
    }

    /**
     * @return Generator<string,array>
     */
    public static function dataProviderServiceIdMustBeNonEmptyString(): Generator
    {
        yield 'ServiceIdMustBeNonEmptyStringException@alias' => [
            static fn (Container $container) => $container->alias('empty-service', ''),
        ];

        yield 'ServiceIdMustBeNonEmptyStringException@bind-empty-abstract' => [
            static fn (Container $container) => $container->bind('', 'empty-abstract'),
        ];

        yield 'ServiceIdMustBeNonEmptyStringException@bind-empty-concrete' => [
            static fn (Container $container) => $container->bind('empty-concrete', ''),
        ];

        yield 'ServiceIdMustBeNonEmptyStringException@build' => [
            static fn (Container $container) => $container->build(''),
        ];

        yield 'ServiceIdMustBeNonEmptyStringException@extend' => [
            static fn (Container $container) => $container->extend(
                '',
                static fn (Container $container): Container => $container
            ),
        ];

        yield 'ServiceIdMustBeNonEmptyStringException@set' => [
            static fn (Container $container) => $container->set('', 'empty-key'),
        ];

        yield 'ServiceIdMustBeNonEmptyStringException@remove' => [
            static fn (Container $container) => $container->remove(''),
        ];

        yield 'ServiceIdMustBeNonEmptyStringException@resolve-via-has' => [
            static fn (Container $container): bool => $container->has(''),
        ];
    }

    public static function dataProviderServiceNotFoundException(): Generator
    {
        yield 'ServiceNotFoundException::missingServiceId@get' => [
            static fn (Container $container): string => $container->get('dose-not-exist'),
        ];

        yield 'ServiceNotFoundException::missingServiceId@alias' => [
            static fn (Container $container) => $container->alias('alias', 'dose-not-exist'),
        ];

        yield 'ServiceNotFoundException::missingServiceId@extend' => [
            static function (Container $container): void {
                $container->extend('extend-missing-service', static fn (Container $container) => null);
            },
        ];

        yield 'ServiceNotFoundException::missingServiceId@remove' => [
            static fn (Container $container) => $container->remove('dose-not-exist'),
        ];
    }

    /**
     * @return Generator<string,array>
     */
    public static function dataProviderServices(): Generator
    {
        $object = new stdClass();
        $closure = static fn (Container $container): string => 'closure-called';

        yield 'object' => ['object', $object, $object];
        yield 'null' => ['null', null, null];
        yield 'int' => ['int', 42, 42];
        yield 'float' => ['float', 4.2, 4.2];
        yield 'true' => ['true', true, true];
        yield 'false' => ['false', false, false];
        yield 'string' => ['string', 'string-value', 'string-value'];
        yield 'array' => ['array', ['array-value'], ['array-value']];
        yield 'callable' => ['closure', $closure, 'closure-called'];
    }

    public static function dataProviderServiceTagMustBeNonEmptyStringException(): Generator
    {
        yield 'ServiceTagMustBeNonEmptyStringException@empty' => [
            static fn (Container $container) => $container->tag(Container::class, ['']),
        ];
        yield 'ServiceTagMustBeNonEmptyStringException@tag' => [
            static fn (Container $container) => $container->tag('', ['tag-1', 'tag-2']),
        ];

        yield 'ServiceTagMustBeNonEmptyStringException@tag-with-empty-tags' => [
            static fn (Container $container) => $container->tag('', ['']),
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    public function testCircularDependencyException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage(sprintf(
            'Circular dependency: %s',
            implode(
                ' -> ',
                [
                    ClassA::class,
                    ClassB::class,
                    ClassC::class,
                    ClassX::class,
                    ClassY::class,
                    ClassZ::class,
                    ClassA::class,
                ]
            )
        ));

        $this->container->build(ClassA::class);
    }

    public function testClassDoseNotExistException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ReflectorException::class);
        $this->expectExceptionMessage('Class "dose-not-exist" does not exist');

        $this->container->build('dose-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        self::assertFalse($this->container->has(stdClass::class));

        $this->container->bind(stdClass::class);

        self::assertTrue($this->container->has(stdClass::class));

        self::assertFalse($this->container->has('class'));

        $this->container->alias('class', stdClass::class);

        self::assertTrue($this->container->has('class'));

        self::assertInstanceOf(stdClass::class, $this->container->get('class'));
    }

    /**
     * @throws Throwable
     */
    public function testContainerBind(): void
    {
        self::assertFalse($this->container->has(Baz::class));
        $this->container->bind(DummyInterface::class, Foo::class, ['taggable']);
        $this->container->bind(Baz::class);

        self::assertTrue($this->container->has(DummyInterface::class));
        self::assertTrue($this->container->has(Baz::class));

        self::assertInstanceOf(Foo::class, $this->container->get(DummyInterface::class));
        self::assertInstanceOf(Baz::class, $this->container->get(Baz::class));
        self::assertCount(1, iterator_to_array($this->container->tagged('taggable')));
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @throws Throwable
     */
    #[DataProvider('dataProviderServiceClasses')]
    public function testContainerBuild(string $class, array $arguments = []): void
    {
        $buildService = $this->container->build($class, $arguments);

        $getService = $this->container->get($class);

        self::assertSame($buildService, $getService);

        if (! array_key_exists('value', $arguments)) {
            return;
        }

        self::assertSame($arguments['value'], $this->container->get($class)->value());
    }

    /**
     * @param callable():void $callback
     *
     * @throws Throwable
     */
    #[DataProvider('dataProviderContainerCallables')]
    public function testContainerCall(callable $callback): void
    {
        $testEvent = $this->container->get(TestEvent::class);

        self::assertSame([], $testEvent->all());
        $expectedCount = random_int(10, 50);
        $actual1 = $expectedCount;
        $actual2 = $expectedCount;

        while ($actual1--) {
            $this->container->call($callback, [$testEvent]);
        }

        self::assertCount($expectedCount, $testEvent->all());

        while ($actual2--) {
            $this->container->call($callback, [$testEvent]);
        }

        self::assertCount($expectedCount * 2, $testEvent->all());

        $this->container->remove(TestEvent::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerCallDefaultValueAvailable(): void
    {
        self::assertSame('Untitled Text', $this->container->call(Dummy::class));
        self::assertSame('#BlackLivesMatter', $this->container->call(Dummy::class, [[], '#BlackLivesMatter']));
        self::assertSame('#BlackLivesMatter', $this->container->call(Dummy::class, [['#BlackLivesMatter'], '%s']));
        self::assertSame(
            '#BlackLivesMatter',
            $this->container->call(Dummy::class, [
                'data' => [],
                'text' => '#BlackLivesMatter',
            ])
        );
        self::assertSame(
            '#BlackLivesMatter',
            $this->container->call(Dummy::class, [
                'data' => ['BlackLivesMatter'],
                'text' => '#%s',
            ])
        );
    }

    public function testContainerConstruct(): void
    {
        self::assertSame($this->container, Container::getInstance());
    }

    /**
     * @throws Throwable
     */
    public function testContainerDestruct(): void
    {
        $this->container->set('test', true);

        self::assertTrue($this->container->has('test'));

        $this->container->__destruct();

        self::assertFalse($this->container->has('test'));
    }

    /**
     * @throws Throwable
     */
    public function testContainerExtend(): void
    {
        $this->container->set('extend', true);

        $this->container->bind(stdClass::class);

        $this->container->extend(
            stdClass::class,
            static function (Container $container, object $stdClass): stdClass {
                $stdClass->one = $container->get('extend');

                return $stdClass;
            }
        );

        $this->container->extend(
            stdClass::class,
            static function (Container $container, object $stdClass): stdClass {
                $stdClass->two = $container->get('extend');

                return $stdClass;
            }
        );

        self::assertTrue($this->container->get(stdClass::class)->one);
        self::assertTrue($this->container->get(stdClass::class)->two);
    }

    public function testContainerImplementsContainerInterface(): void
    {
        self::assertTrue(is_subclass_of(Container::class, ContainerInterface::class));
        self::assertInstanceOf(ContainerInterface::class, $this->container);
        self::assertInstanceOf(Container::class, $this->container);
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegister(): void
    {
        $this->container->register(FoobarServiceProvider::class);

        self::assertTrue($this->container->has(Foo::class));
        self::assertTrue($this->container->has(Bar::class));
        self::assertTrue($this->container->has(Baz::class));

        self::assertInstanceOf(stdClass::class, $this->container->get('foobar'));
    }

    /**
     * @throws Throwable
     */
    public function testContainerRemove(): void
    {
        $this->container->register(FoobarServiceProvider::class);

        self::assertTrue($this->container->has(Foo::class));
        self::assertTrue($this->container->has(Bar::class));
        self::assertTrue($this->container->has(Baz::class));

        $this->container->remove(Foo::class);
        $this->container->remove(Bar::class);
        $this->container->remove(Baz::class);

        self::assertFalse($this->container->has(Foo::class));
        self::assertFalse($this->container->has(Bar::class));
        self::assertFalse($this->container->has(Baz::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerReset(): void
    {
        $this->container->register(FoobarServiceProvider::class);

        self::assertTrue($this->container->has(Foo::class));
        self::assertTrue($this->container->has(Bar::class));
        self::assertTrue($this->container->has(Baz::class));

        $foo = $this->container->get(Foo::class);
        $bar = $this->container->get(Bar::class);
        $baz = $this->container->get(Baz::class);

        $this->container->replace(Foo::class, $this->container->build(Foo::class));
        $this->container->replace(Bar::class, $this->container->build(Bar::class));
        $this->container->replace(Baz::class, $this->container->build(Baz::class));

        self::assertInstanceOf(Foo::class, $this->container->get(Foo::class));
        self::assertInstanceOf(Bar::class, $this->container->get(Bar::class));
        self::assertInstanceOf(Baz::class, $this->container->get(Baz::class));

        self::assertNotSame($foo, $this->container->get(Foo::class));
        self::assertNotSame($bar, $this->container->get(Bar::class));
        self::assertNotSame($baz, $this->container->get(Baz::class));
    }

    /**
     * @param null|bool|Closure():null|float|int|stdClass|string|string[] $value
     * @param null|bool|float|int|stdClass|string|string[] $expected
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[DataProvider('dataProviderServices')]
    public function testContainerSet(
        string $key,
        null|bool|float|int|stdClass|string|array|Closure $value,
        null|bool|float|int|stdClass|string|array $expected
    ): void {
        $this->container->set($key, $value);
        self::assertSame($expected, $this->container->get($key));
    }

    /**
     * It should register and retrieve tagged services IDs with attributes.
     *
     * @throws Throwable
     */
    public function testContainerTag(): void
    {
        $this->container->set('stdclass1', static fn (Container $container): string => 'first-tag', ['tag-1']);
        $this->container->set('stdclass2', static fn (Container $container): string => 'first-tag', ['tag-1']);

        $stdClass3 = new stdClass();
        $stdClass4 = new stdClass();
        $this->container->set('stdclass3', static fn (Container $container): stdClass => $stdClass3, ['tag-2']);
        $this->container->set('stdclass4', static fn (Container $container): stdClass => $stdClass4);
        $this->container->tag('stdclass4', ['tag-2']);

        self::assertCount(2, iterator_to_array($this->container->tagged('tag-1')));
        self::assertCount(2, iterator_to_array($this->container->tagged('tag-2')));

        foreach ($this->container->tagged('tag-1') as $serviceId) {
            self::assertSame('first-tag', $serviceId);
        }

        self::assertContainsOnlyInstancesOf(stdClass::class, $this->container->tagged('tag-2'));

        self::assertSame([$stdClass3, $stdClass4], iterator_to_array($this->container->tagged('tag-2')));
    }

    public function testDontCloneException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Dont clone "Ghostwriter\Container\Container".');

        $this->container->set('clone', clone $this->container);
    }

    public function testDontSerializeException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Dont serialize "Ghostwriter\Container\Container".');

        self::assertNull(serialize($this->container));
    }

    public function testDontUnserializeException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Dont unserialize "Ghostwriter\Container\Container".');
        self::assertNull(
            unserialize(
                // mocks a serialized Container::class
                sprintf('O:%s:"%s":0:{}', mb_strlen($this->container::class), $this->container::class)
            )
        );
    }

    public function testNotFoundExceptionImplementsContainerNotFoundExceptionInterface(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Service "not-found" was not found.');

        $this->container->get('not-found');
    }

    public function testNotInstantiableException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Class "Throwable" is not instantiable.');

        $this->container->build(Throwable::class);
    }

    /**
     * @param callable(Container):void $test
     *
     * @throws ContainerExceptionInterface
     */
    #[DataProvider('dataProviderServiceAliasMustBeNonEmptyStringException')]
    public function testServiceAliasMustBeNonEmptyStringException(callable $test): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);

        $test($this->container);
    }

    /**
     * @param callable(Container):void $test
     *
     * @throws ContainerExceptionInterface
     */
    #[DataProvider('dataProviderServiceAlreadyRegisteredException')]
    public function testServiceAlreadyRegisteredException(callable $test): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);

        $test($this->container);
    }

    public function testServiceCannotAliasItselfException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Service "Ghostwriter\Container\Contract\ServiceProviderInterface" can not use an alias with the same name.'
        );

        $this->container->alias(ServiceProviderInterface::class, ServiceProviderInterface::class);
    }

    /**
     * @param callable(Container):void $test
     *
     * @throws ContainerExceptionInterface
     */
    #[DataProvider('dataProviderServiceIdMustBeNonEmptyString')]
    public function testServiceIdMustBeNonEmptyStringException(callable $test): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Service Id MUST be a non-empty-string.');

        $test($this->container);
    }

    #[DataProvider('dataProviderServiceNotFoundException')]
    public function testServiceNotFoundException(callable $test): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessageMatches(
            '#Service "(alias|extend-missing-service|dose-not-exist)" was not found.#'
        );

        $test($this->container);
    }

    public function testServiceProviderAlreadyRegisteredException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage(
            sprintf('ServiceProvider "%s" is already registered.', FoobarServiceProvider::class)
        );

        $this->container->register(FoobarServiceProvider::class);
        $this->container->register(FoobarServiceProvider::class);
    }

    public function testServiceProvidersMustImplementServiceProviderInterfaceException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(
            sprintf(
                'ServiceProvider "%s" MUST implement "%s".',
                ServiceProviderInterface::class,
                ServiceProviderInterface::class
            )
        );

        $this->container->register(ServiceProviderInterface::class);
    }

    #[DataProvider('dataProviderServiceTagMustBeNonEmptyStringException')]
    public function testServiceTagMustBeNonEmptyStringException(callable $test): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Service Id MUST be a non-empty-string.');

        $test($this->container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    public function testUnresolvableParameterExceptionBuild(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable class parameter "$number" in "%s::%s"; does not have a default value.',
            UnresolvableParameter::class,
            '__construct()'
        ));

        $this->container->build(UnresolvableParameter::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    public function testUnresolvableParameterExceptionCall(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable function parameter "%s" in "%s"; does not have a default value.',
            '$event',
            'Ghostwriter\Container\Tests\Fixture\typelessFunction()',
        ));

        $this->container->call('Ghostwriter\Container\Tests\Fixture\typelessFunction');
    }
}
