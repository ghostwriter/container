<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use ArrayAccess;
use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Contract\ContainerExceptionInterface;
use Ghostwriter\Container\Contract\ContainerInterface;
use Ghostwriter\Container\Contract\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Contract\ServiceProviderInterface;
use Ghostwriter\Container\Exception\BadMethodCallException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\InvalidArgumentException;
use Ghostwriter\Container\Exception\LogicException;
use Ghostwriter\Container\Exception\NotFoundException;
use Ghostwriter\Container\Exception\NotInstantiableException;
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
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Container\ContainerExceptionInterface as PsrContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface as PsrNotFoundExceptionInterface;
use stdClass;
use Throwable;
use function array_key_exists;
use function is_subclass_of;
use function random_int;
use function serialize;
use function sprintf;
use function strlen;
use function unserialize;

/**
 * @coversDefaultClass \Ghostwriter\Container\Container
 *
 * @internal
 *
 * @small
 */
final class ContainerTest extends PHPUnitTestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = Container::getInstance();
    }

    protected function tearDown(): void
    {
        $this->container->__destruct();
    }

    /**
     * @psalm-return Generator<string,array>
     */
    public function dataProviderContainerCallables(): Generator
    {
        yield 'TypelessAnonymousFunctionCall' => [
            static function ($event): void {
                $event->collect($event::class);
            },
            [
                'event'=>new TestEvent(),
            ],
        ];

        yield 'AnonymousFunctionCall' => [static function (TestEvent $event): void {
            $event->collect($event::class);
        }];

        yield 'FunctionCall@typedFunction' => ['Ghostwriter\Container\Tests\Fixture\typedFunction'];
        yield 'FunctionCall@typelessFunction' => [
            'Ghostwriter\Container\Tests\Fixture\typelessFunction', [
                'event'=>new TestEvent(),

            ], ];

        yield 'StaticMethodCall' => [TestEventListener::class . '::onStatic'];

        yield 'CallableArrayStaticMethodCall' => [[TestEventListener::class, 'onStaticCallableArray'], [
            'nullable' =>null,
        ]];

        yield 'CallableArrayInstanceMethodCall' => [[new TestEventListener(), 'onTest']];

        yield 'Invokable' => [new TestEventListener()];
    }

    /**
     * @psalm-return Generator<string,array>
     */
    public function dataProviderContainerExceptions(): Generator
    {
        yield 'CircularDependencyException::detected' => [
            CircularDependencyException::class,
            CircularDependencyException::detected(
                ClassA::class,
                [ClassA::class, ClassB::class, ClassC::class, ClassX::class, ClassY::class, ClassZ::class]
            )->getMessage(),
            static function (Container $container): void {
                $container->build(ClassA::class);
            },
        ];

        yield 'InvalidArgumentException::emptyServiceId@alias' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceId()->getMessage(),
            static function (Container $container): void {
                $container->alias('empty-value', '');
            },
        ];

        yield 'InvalidArgumentException::emptyServiceId@bind' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceId()->getMessage(),
            static function (Container $container): void {
                $container->bind('', 'empty-value');
            },
        ];

        yield 'InvalidArgumentException::emptyServiceId@extend' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceId()->getMessage(),
            static function (Container $container): void {
                $container->extend('', static fn (Container $container) => $container);
            },
        ];

        yield 'InvalidArgumentException::emptyServiceId@set' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceId()->getMessage(),
            static function (Container $container): void {
                $container->set('', 'empty-key');
            },
        ];

        yield 'InvalidArgumentException::emptyServiceId@remove' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceId()->getMessage(),
            static function (Container $container): void {
                $container->remove('');
            },
        ];

        yield 'InvalidArgumentException::emptyServiceId@resolve-via-has' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceId()->getMessage(),
            static function (Container $container): void {
                $container->has('');
            },
        ];

        yield 'InvalidArgumentException::emptyServiceId@tag' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceId()->getMessage(),
            static function (Container $container): void {
                $container->tag('', ['']);
            },
        ];

        yield 'InvalidArgumentException::emptyServiceAlias' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceAlias()->getMessage(),
            static function (Container $container): void {
                $container->alias('', 'empty-key');
            },
        ];

        yield 'InvalidArgumentException::emptyServiceTagForServiceId' => [
            InvalidArgumentException::class,
            InvalidArgumentException::emptyServiceTagForServiceId(Container::class)->getMessage(),
            static function (Container $container): void {
                $container->tag(Container::class, ['']);
            },
        ];

        yield 'BadMethodCallException::dontClone' => [
            BadMethodCallException::class,
            BadMethodCallException::dontClone(Container::class)->getMessage(),
            static function (Container $container): void {
                $container->set('clone', clone $container);
            },
        ];

        yield 'BadMethodCallException::dontSerialize' => [
            BadMethodCallException::class,
            BadMethodCallException::dontSerialize(Container::class)->getMessage(),
            static function (Container $container): void {
                serialize($container);
            },
        ];

        yield 'BadMethodCallException::dontUnserialize' => [
            BadMethodCallException::class,
            BadMethodCallException::dontUnserialize(Container::class)->getMessage(),
            static function (Container $container): void {
                unserialize(
                    // mocks a serialized Container::class
                    sprintf('O:%s:"%s":0:{}', strlen($container::class), $container::class)
                );
            },
        ];

        yield 'LogicException::serviceAlreadyRegistered' => [
            LogicException::class,
            LogicException::serviceAlreadyRegistered(Container::class)->getMessage(),
            static fn (Container $container) => $container->set(Container::class, $container),
        ];

        yield 'LogicException::serviceAlreadyRegistered@bind' => [
            LogicException::class,
            LogicException::serviceAlreadyRegistered('bind')->getMessage(),
            static function (Container $container): void {
                $container->set('bind', 'empty-value');
                $container->bind('bind', stdClass::class);
            },
        ];

        yield 'LogicException::serviceCannotAliasItself' => [
            LogicException::class,
            LogicException::serviceCannotAliasItself(ServiceProviderInterface::class)->getMessage(),
            static fn (Container $container) => $container->alias(
                ServiceProviderInterface::class,
                ServiceProviderInterface::class
            ),
        ];

        yield 'LogicException::serviceExtensionAlreadyRegistered' => [
            LogicException::class,
            LogicException::serviceExtensionAlreadyRegistered(FoobarExtension::class)->getMessage(),
            static function (Container $container): void {
                $container->bind(stdClass::class);

                $extension = $container->get(FoobarExtension::class);
                $container->add(stdClass::class, $extension);
                $container->add(stdClass::class, $extension);
            },
        ];

        yield 'LogicException::serviceProviderAlreadyRegistered' => [
            LogicException::class,
            LogicException::serviceProviderAlreadyRegistered(FoobarServiceProvider::class)->getMessage(),
            static function (Container $container): void {
                /**
                 * Service providers are automatically registered when FQCN requested via `build` or `get`.
                 *
                 * if you register it again,it fails.
                 */
                // $serviceProvider = $container->get(FoobarServiceProvider::class);
                $serviceProvider = $container->build(FoobarServiceProvider::class);
                $container->register($serviceProvider);
            },
        ];

        yield 'NotFoundException::missingServiceId@get' => [
            NotFoundException::class,
            NotFoundException::notRegistered('dose-not-exist')->getMessage(),
            static function (Container $container): void {
                $container->get('dose-not-exist');
            },
        ];

        yield 'NotFoundException::missingServiceId@alias' => [
            NotFoundException::class,
            NotFoundException::notRegistered('dose-not-exist')->getMessage(),
            static function (Container $container): void {
                $container->alias('alias', 'dose-not-exist');
            },
        ];

        yield 'NotFoundException::missingServiceId@extend' => [
            NotFoundException::class,
            NotFoundException::notRegistered(stdClass::class)->getMessage(),
            static function (Container $container): void {
                $container->extend(stdClass::class, static fn (Container $container) => null);
            },
        ];

        yield 'NotFoundException::missingServiceId@remove' => [
            NotFoundException::class,
            NotFoundException::notRegistered('dose-not-exist')->getMessage(),
            static function (Container $container): void {
                $container->remove('dose-not-exist');
            },
        ];

        yield 'NotInstantiableException::abstractClassOrInterface' => [
            NotInstantiableException::class,
            NotInstantiableException::abstractClassOrInterface(Throwable::class)->getMessage(),
            static function (Container $container): void {
                $container->build(Throwable::class);
            },
        ];

        yield 'NotInstantiableException::classDoseNotExist' => [
            NotInstantiableException::class,
            NotInstantiableException::classDoseNotExist('dose-not-exist')->getMessage(),
            static function (Container $container): void {
                $container->build('dose-not-exist');
            },
        ];

        yield 'NotInstantiableException::unresolvableParameter' => [
            NotInstantiableException::class,
            NotInstantiableException::unresolvableParameter(
                'number',
                UnresolvableParameter::class,
                '__construct'
            )->getMessage(),
            static function (Container $container): void {
                $container->build(UnresolvableParameter::class);
            },
        ];

        yield 'NotInstantiableException::unresolvableParameter@call-function' => [
            NotInstantiableException::class,
            NotInstantiableException::unresolvableParameter(
                'event',
                null,
                'Ghostwriter\Container\Tests\Fixture\typelessFunction',
            )->getMessage(),
            static function (Container $container): void {
                $container->invoke('Ghostwriter\Container\Tests\Fixture\typelessFunction');
            },
        ];

        yield 'NotInstantiableException::unresolvableParameter@invoke-class' => [
            NotInstantiableException::class,
            NotInstantiableException::unresolvableParameter(
                'nullable',
                TestEventListener::class,
                'onStaticCallableArray'
            )->getMessage(),
            static function (Container $container): void {
                $container->invoke([TestEventListener::class, 'onStaticCallableArray']);
            },
        ];
        yield 'NotInstantiableException::unresolvableParameter@invoke-undefind-class' => [
            NotInstantiableException::class,
            NotInstantiableException::unresolvableParameter(
                'nullable',
                TestEventListener::class,
                'onStaticCallableArray'
            )->getMessage(),
            static function (Container $container): void {
                $container->invoke(TestEventListener::class);
            },
        ];
    }

    /** @return iterable<string,array> */
    public function dataProviderPropertyAccessorMagicMethods(): iterable
    {
        foreach (['__get', '__isset', '__set', '__unset'] as $method) {
            yield $method => [$method];
        }
    }

    /** @return iterable<string,array> */
    public function dataProviderServiceClasses(): iterable
    {
        yield ArrayConstructor::class => [ArrayConstructor::class, [
            'value' => [],
        ]];
        yield BoolConstructor::class => [BoolConstructor::class, [
            'value' => true,
        ]];
        yield CallableConstructor::class => [CallableConstructor::class, [
            'value' => static fn (Container $container) => null,
        ]];
        yield EmptyConstructor::class => [EmptyConstructor::class];
        yield FloatConstructor::class => [FloatConstructor::class, [
            'value' => 13.37,
        ]];
        yield IntConstructor::class => [IntConstructor::class, [
            'value' => 42,
        ]];
        yield IterableConstructor::class => [IterableConstructor::class, [
            'value' => ['iterable'],
        ]];
        yield MixedConstructor::class => [MixedConstructor::class, [
            'value' => 'mixed',
        ]];
        yield ObjectConstructor::class => [ObjectConstructor::class, [
            'value' => new stdClass(),
        ]];
        yield OptionalConstructor::class => [OptionalConstructor::class];
        yield StringConstructor::class => [StringConstructor::class, [
            'value' => 'string',
        ]];
        yield TypelessConstructor::class => [TypelessConstructor::class, [
            'value' => 'none',
        ]];
        yield UnionTypehintWithoutDefaultValue::class => [UnionTypehintWithoutDefaultValue::class, [
            'number' => 42,
        ]];
        yield UnionTypehintWithDefaultValue::class => [UnionTypehintWithDefaultValue::class];
        yield Foo::class => [Foo::class];
        yield Bar::class => [Bar::class];
        yield Baz::class => [Baz::class];
        yield Container::class => [Container::class];
        yield FoobarWithDependencyServiceProvider::class => [FoobarWithDependencyServiceProvider::class];
        yield FoobarServiceProvider::class => [FoobarServiceProvider::class];
        yield FoobarExtension::class => [FoobarExtension::class];
        yield self::class => [self::class];
    }

    /** @return iterable<string,array> */
    public function dataProviderServices(): iterable
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

    /**
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::add
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::build
     * @covers \Ghostwriter\Container\Container::extend
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::resolve
     *
     * @throws Throwable
     */
    public function testContainerAdd(): void
    {
        $this->container->bind('extendable', stdClass::class);

        $this->container->add('extendable', new FoobarExtension());

        $extendable = $this->container->get('extendable');

        self::assertInstanceOf(Foo::class, $extendable->foo);
        self::assertInstanceOf(Bar::class, $extendable->bar);
    }

    /**
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::alias
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::build
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::set
     *
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
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::build
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::resolve
     *
     * @throws Throwable
     */
    public function testContainerBind(): void
    {
        $this->container->bind(DummyInterface::class, Foo::class);
        $this->container->bind(Baz::class);

        self::assertTrue($this->container->has(DummyInterface::class));
        self::assertTrue($this->container->has(Baz::class));

        self::assertInstanceOf(Foo::class, $this->container->get(DummyInterface::class));
        self::assertInstanceOf(Baz::class, $this->container->get(Baz::class));
    }

    /**
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::add
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::build
     * @covers \Ghostwriter\Container\Container::extend
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::register
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::set
     * @dataProvider dataProviderServiceClasses
     *
     * @param array<string, mixed> $arguments
     *
     * @throws Throwable
     */
    public function testContainerBuild(string $class, array $arguments = []): void
    {
        $buildService = $this->container->build($class, $arguments);

        $getService = $this->container->get($class);

        self::assertSame($buildService, $getService);

        if (array_key_exists('value', $arguments)) {
            self::assertSame($arguments['value'], $this->container->get($class)->value());
        }
    }

    /**
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::invoke
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::set
     *
     * @dataProvider dataProviderContainerCallables
     *
     * @throws Throwable
     */
    public function testContainerCall(callable $callback, array $arguments = []): void
    {
        $this->container->set(TestEvent::class, $arguments['event'] ?? new TestEvent());

        $actual = $expectedCount = random_int(10, 50);

        while ($actual) {
            $this->container->invoke($callback, $arguments);
            --$actual;
        }

        self::assertCount($expectedCount, $this->container->get(TestEvent::class)->all());
    }

    /**
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::getInstance
     */
    public function testContainerConstruct(): void
    {
        self::assertSame($this->container, Container::getInstance());
    }

    /**
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::set
     *
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
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::build
     * @covers \Ghostwriter\Container\Container::extend
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::set
     *
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

    /**
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::offsetExists
     * @covers \Ghostwriter\Container\Container::offsetGet
     * @covers \Ghostwriter\Container\Container::offsetSet
     * @covers \Ghostwriter\Container\Container::offsetUnset
     * @covers \Ghostwriter\Container\Container::remove
     * @covers \Ghostwriter\Container\Container::set
     *
     * @throws Throwable
     */
    public function testContainerImplementsArrayAccessInterface(): void
    {
        self::assertInstanceOf(ArrayAccess::class, $this->container);

        self::assertArrayNotHasKey(__METHOD__, $this->container);

        $this->container[__METHOD__] = true;

        self::assertArrayHasKey(__METHOD__, $this->container);

        self::assertTrue($this->container[__METHOD__]);

        unset($this->container[__METHOD__]);

        self::assertArrayNotHasKey(__METHOD__, $this->container);
    }

    /**
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::__get
     * @covers \Ghostwriter\Container\Container::__isset
     * @covers \Ghostwriter\Container\Container::__set
     * @covers \Ghostwriter\Container\Container::__unset
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::remove
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::set
     * @dataProvider dataProviderPropertyAccessorMagicMethods
     *
     * @throws Throwable
     */
    public function testContainerImplementsPropertyAccessorMagicMethods(string $method): void
    {
        self::assertTrue(method_exists($this->container, $method));

        $this->container->{$method} = true;

        self::assertTrue(isset($this->container->{$method}));

        self::assertTrue($this->container->{$method});

        unset($this->container->{$method});

        self::assertFalse(isset($this->container->{$method}));
    }

    /**
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::getInstance
     */
    public function testContainerImplementsPsrContainerInterface(): void
    {
        self::assertTrue(is_subclass_of(ContainerInterface::class, PsrContainerInterface::class));
        self::assertTrue(is_subclass_of(Container::class, PsrContainerInterface::class));
        self::assertTrue(is_subclass_of(Container::class, ContainerInterface::class));

        self::assertInstanceOf(PsrContainerInterface::class, $this->container);
        self::assertInstanceOf(ContainerInterface::class, $this->container);
        self::assertInstanceOf(Container::class, $this->container);
    }

    /**
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::add
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::build
     * @covers \Ghostwriter\Container\Container::extend
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::register
     * @covers \Ghostwriter\Container\Container::resolve
     *
     * @throws Throwable
     */
    public function testContainerRegister(): void
    {
        $this->container->register(new FoobarServiceProvider());

        self::assertTrue($this->container->has(Foo::class));
        self::assertTrue($this->container->has(Bar::class));
        self::assertTrue($this->container->has(Baz::class));

        self::assertInstanceOf(stdClass::class, $this->container->get('foobar'));
    }

    /**
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::add
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::build
     * @covers \Ghostwriter\Container\Container::extend
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::register
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::remove
     *
     * @throws Throwable
     */
    public function testContainerRemove(): void
    {
        $this->container->register(new FoobarServiceProvider());

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
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::alias
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::set
     * @dataProvider dataProviderServices
     *
     * @throws PsrNotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws PsrContainerExceptionInterface
     */
    public function testContainerSet(string $key, mixed $value, mixed $expected): void
    {
        $this->container->set($key, $value);
        self::assertSame($expected, $this->container->get($key));
    }

    /**
     * It should register and retrieve tagged services IDs with attributes.
     *
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::tagged
     * @covers \Ghostwriter\Container\Container::set
     * @covers \Ghostwriter\Container\Container::set
     * @covers \Ghostwriter\Container\Container::tag
     * @covers \Ghostwriter\Container\Exception\InvalidArgumentException::emptyServiceTagForServiceId
     *
     * @throws Throwable
     */
    public function testContainerTag(): void
    {
        $this->container->set('stdclass1', static fn (Container $container): string => 'first-tag', ['tag-1']);

        $this->container->set('stdclass2', static fn (Container $container): string => 'first-tag', ['tag-1']);

        $this->container->set('stdclass3', static fn (Container $container): stdClass => new stdClass(), ['tag-2']);
        $this->container->set('stdclass4', static fn (Container $container): stdClass => new stdClass());
        $this->container->tag('stdclass4', ['tag-2']);

        self::assertNotNull($this->container->tagged('tag-1'));
        self::assertCount(2, $this->container->tagged('tag-1'));
        self::assertCount(2, $this->container->tagged('tag-2'));

        foreach ($this->container->tagged('tag-1') as $serviceId) {
            self::assertSame('first-tag', $this->container->get($serviceId));
        }

        foreach ($this->container->tagged('tag-2') as $serviceId) {
            self::assertInstanceOf(stdClass::class, $this->container->get($serviceId));
        }
    }

    /**
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__clone
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::__serialize
     * @covers \Ghostwriter\Container\Container::__unserialize
     * @covers \Ghostwriter\Container\Container::add
     * @covers \Ghostwriter\Container\Container::alias
     * @covers \Ghostwriter\Container\Container::bind
     * @covers \Ghostwriter\Container\Container::build
     * @covers \Ghostwriter\Container\Container::extend
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::has
     * @covers \Ghostwriter\Container\Container::invoke
     * @covers \Ghostwriter\Container\Container::register
     * @covers \Ghostwriter\Container\Container::remove
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Container::set
     * @covers \Ghostwriter\Container\Container::tag
     * @covers \Ghostwriter\Container\Exception\CircularDependencyException::detected
     * @covers \Ghostwriter\Container\Exception\InvalidArgumentException::emptyServiceId
     * @covers \Ghostwriter\Container\Exception\InvalidArgumentException::emptyServiceAlias
     * @covers \Ghostwriter\Container\Exception\InvalidArgumentException::emptyServiceTagForServiceId
     * @covers \Ghostwriter\Container\Exception\BadMethodCallException::dontClone
     * @covers \Ghostwriter\Container\Exception\BadMethodCallException::dontSerialize
     * @covers \Ghostwriter\Container\Exception\BadMethodCallException::dontUnserialize
     * @covers \Ghostwriter\Container\Exception\LogicException::serviceAlreadyRegistered
     * @covers \Ghostwriter\Container\Exception\LogicException::serviceCannotAliasItself
     * @covers \Ghostwriter\Container\Exception\LogicException::serviceExtensionAlreadyRegistered
     * @covers \Ghostwriter\Container\Exception\LogicException::serviceProviderAlreadyRegistered
     * @covers \Ghostwriter\Container\Exception\NotFoundException::notRegistered
     * @covers \Ghostwriter\Container\Exception\NotInstantiableException::abstractClassOrInterface
     * @covers \Ghostwriter\Container\Exception\NotInstantiableException::classDoseNotExist
     * @covers \Ghostwriter\Container\Exception\NotInstantiableException::unresolvableParameter
     * @dataProvider dataProviderContainerExceptions
     *
     * @param callable(Container):void $test
     *
     * @throws Throwable
     */
    public function testExceptionsImplementPsrContainerExceptionInterface(
        string $exception,
        string $message,
        callable $test
    ): void {
        self::assertTrue(is_subclass_of($exception, PsrContainerExceptionInterface::class));
        self::assertTrue(is_subclass_of($exception, ContainerExceptionInterface::class));

        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        try {
            $test($this->container);
        } catch (Throwable $throwable) {
            if ($exception !== $throwable::class) {
                self::assertSame($exception, $throwable->getMessage());
            }
            self::assertSame($exception, $throwable::class);

            self::assertInstanceOf(PsrContainerExceptionInterface::class, $throwable);
            self::assertInstanceOf(ContainerExceptionInterface::class, $throwable);

            // re-throw to validate the expected exception message.
            throw $throwable;
        }
    }

    /**
     * @covers \Ghostwriter\Container\Container::__construct
     * @covers \Ghostwriter\Container\Container::__destruct
     * @covers \Ghostwriter\Container\Container::getInstance
     * @covers \Ghostwriter\Container\Container::get
     * @covers \Ghostwriter\Container\Container::resolve
     * @covers \Ghostwriter\Container\Exception\NotFoundException::notRegistered
     */
    public function testNotFoundExceptionImplementsPsrContainerNotFoundExceptionInterface(): void
    {
        try {
            $this->container->get('not-found');
        } catch (Throwable $throwable) {
            self::assertInstanceOf(PsrContainerExceptionInterface::class, $throwable);
            self::assertInstanceOf(ContainerExceptionInterface::class, $throwable);

            self::assertInstanceOf(PsrNotFoundExceptionInterface::class, $throwable);
            self::assertInstanceOf(NotFoundExceptionInterface::class, $throwable);
            self::assertInstanceOf(NotFoundException::class, $throwable);
        }
    }
}
