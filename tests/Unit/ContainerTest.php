<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Exception\AliasNameAndServiceNameCannotBeTheSameException;
use Ghostwriter\Container\Exception\AliasNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\CircularDependencyException;
use Ghostwriter\Container\Exception\ClassNotInstantiableException;
use Ghostwriter\Container\Exception\DontCloneContainerException;
use Ghostwriter\Container\Exception\DontSerializeContainerException;
use Ghostwriter\Container\Exception\DontUnserializeContainerException;
use Ghostwriter\Container\Exception\ReflectorException;
use Ghostwriter\Container\Exception\ServiceAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceExtensionAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException;
use Ghostwriter\Container\Exception\ServiceFactoryAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNameMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceNotFoundException;
use Ghostwriter\Container\Exception\ServiceProviderAlreadyRegisteredException;
use Ghostwriter\Container\Exception\ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException;
use Ghostwriter\Container\Exception\ServiceTagMustBeNonEmptyStringException;
use Ghostwriter\Container\Exception\ServiceTagNotFoundException;
use Ghostwriter\Container\Exception\UnresolvableParameterException;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\Bar;
use Ghostwriter\Container\Tests\Fixture\Baz;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassA;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassB;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassC;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassX;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassY;
use Ghostwriter\Container\Tests\Fixture\CircularDependency\ClassZ;
use Ghostwriter\Container\Tests\Fixture\ClientInterface;
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
use Ghostwriter\Container\Tests\Fixture\DummyFactory;
use Ghostwriter\Container\Tests\Fixture\DummyInterface;
use Ghostwriter\Container\Tests\Fixture\Extension\FoobarExtension;
use Ghostwriter\Container\Tests\Fixture\Extension\StdClassOneExtension;
use Ghostwriter\Container\Tests\Fixture\Extension\StdClassTwoExtension;
use Ghostwriter\Container\Tests\Fixture\Foo;
use Ghostwriter\Container\Tests\Fixture\Foobar;
use Ghostwriter\Container\Tests\Fixture\GitHub;
use Ghostwriter\Container\Tests\Fixture\GitHubClient;
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
use stdClass;
use Throwable;

use function array_key_exists;
use function sprintf;

#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
#[CoversClass(ServiceAlreadyRegisteredException::class)]
#[CoversClass(ServiceFactoryAlreadyRegisteredException::class)]
#[CoversClass(ServiceNameMustBeNonEmptyStringException::class)]
#[CoversClass(ServiceNotFoundException::class)]
#[CoversClass(ServiceProviderAlreadyRegisteredException::class)]
final class ContainerTest extends AbstractTestCase
{
    private ContainerInterface $container;

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
                'value' => static fn (ContainerInterface $container): null => null,
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

    public function expectExceptionInterface(string $exception): void
    {
        $this->expectException(Throwable::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException($exception);
    }

    public function expectNotFoundExceptionInterface(string $exception): void
    {
        $this->expectException(Throwable::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException($exception);
    }

    public function testAliasNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(AliasNameMustBeNonEmptyStringException::class);

        $this->container->alias('', 'service');
    }

    public function testAliasNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(AliasNameMustBeNonEmptyStringException::class);

        $this->container->alias('             ', 'service');
    }

    public function testAliasThrowsAliasNameAndServiceNameCannotBeTheSameException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(AliasNameAndServiceNameCannotBeTheSameException::class);
        $this->expectExceptionMessage(ServiceProviderInterface::class);

        $this->container->alias(ServiceProviderInterface::class, ServiceProviderInterface::class);
    }

    public function testBindAbstractThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(stdClass::class, '', stdClass::class);
    }

    public function testBindAbstractThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(stdClass::class, '         ', stdClass::class);
    }

    public function testBindAbstractThrowsServiceNotFoundException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, 'not-a-class', stdClass::class);
    }

    public function testBindConcreteThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind('', stdClass::class, stdClass::class);
    }
    public function testBindConcreteThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind('     ', stdClass::class, stdClass::class);
    }

    public function testBindConcreteThrowsServiceNotFoundException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNotFoundException::class);

        $this->container->bind('not-a-class', stdClass::class, stdClass::class);
    }

    public function testBindImplementationThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(stdClass::class, stdClass::class, '');
    }
    public function testBindImplementationThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->bind(stdClass::class, stdClass::class, '     ');
    }

    public function testBindImplementationThrowsServiceNotFoundException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNotFoundException::class);

        $this->container->bind(stdClass::class, stdClass::class, 'not-a-class');
    }

    public function testBuildThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->build('');
    }

    public function testBuildThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->build('     ');
    }

    public function testCircularDependencyException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage(sprintf(
            'Class: %s',
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

    public function testClassDoesNotExistException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ReflectorException::class);
        $this->expectExceptionMessage('Class "does-not-exist" does not exist');

        $this->container->build('does-not-exist');
    }

    /**
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        self::assertFalse($this->container->has(stdClass::class));

        $std = new stdClass();

        $this->container->set(stdClass::class, $std);

        self::assertTrue($this->container->has(stdClass::class));

        self::assertFalse($this->container->has('class'));

        $this->container->alias('class', stdClass::class);

        self::assertTrue($this->container->has('class'));

        self::assertSame($std, $this->container->get('class'));
    }

    public function testContainerBind(): void
    {
        self::assertFalse($this->container->has(GitHub::class));
        self::assertFalse($this->container->has(ClientInterface::class));
        self::assertFalse($this->container->has(GitHubClient::class));

        // When GitHub::class asks for ClientInterface::class, resolve GitHubClient::class.
        $this->container->bind(
            GitHub::class,
            ClientInterface::class,
            GitHubClient::class
        );

        self::assertTrue(
            $this->container->has(GitHubClient::class)
        );

        self::assertInstanceOf(GitHub::class, $this->container->get(GitHub::class));

        self::assertInstanceOf(ClientInterface::class, $this->container->get(GitHub::class)->getClient());

        self::assertTrue($this->container->has(GitHubClient::class));
        self::assertTrue($this->container->has(GitHub::class));
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

    public function testContainerBuildServiceProviderDoesNotRegisterServiceProvider(): void
    {
        $foobarServiceProvider = $this->container->build(FoobarServiceProvider::class);
        self::assertInstanceOf(FoobarServiceProvider::class, $foobarServiceProvider);

        $second = $this->container->build(FoobarServiceProvider::class);
        self::assertInstanceOf(FoobarServiceProvider::class, $second);

        self::assertNotSame($foobarServiceProvider, $second);

        self::assertFalse($this->container->has(Foo::class));
        self::assertFalse($this->container->has(Bar::class));
        self::assertFalse($this->container->has(Baz::class));

        $this->container->provide(FoobarServiceProvider::class);

        self::assertTrue($this->container->has(Foo::class));
        self::assertTrue($this->container->has(Bar::class));
        self::assertTrue($this->container->has(Baz::class));
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

    public function testContainerConstruct(): void
    {
        self::assertSame(Container::getInstance(), Container::getInstance());
    }

    /**
     * @throws Throwable
     */
    public function testContainerDestruct(): void
    {
        $this->container->set('test', static fn (): bool => true);

        self::assertTrue($this->container->has('test'));

        $this->container->__destruct();

        self::assertFalse($this->container->has('test'));
    }

    /**
     * @throws Throwable
     */
    public function testContainerExtend(): void
    {
        $this->container->extend(
            stdClass::class,
            StdClassOneExtension::class
        );

        $this->container->extend(
            stdClass::class,
            StdClassTwoExtension::class
        );

        self::assertInstanceOf(
            stdClass::class,
            $this->container->get(stdClass::class)
        );

        self::assertInstanceOf(
            stdClass::class,
            $this->container->get(stdClass::class)->one
        );

        self::assertInstanceOf(
            stdClass::class,
            $this->container->get(stdClass::class)->two
        );
    }

    public function testGetThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->get('');
    }
    public function testGetThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->get('    ');
    }

    public function testContainerImplementsContainerInterface(): void
    {
        $container = Container::getInstance();

        self::assertTrue(is_subclass_of(Container::class, ContainerInterface::class));
        self::assertInstanceOf(ContainerInterface::class, $container);
        self::assertInstanceOf(Container::class, $container);
    }

    public function testContainerInvokeDefaultValueAvailable(): void
    {
        self::assertSame('Untitled Text', $this->container->invoke(Dummy::class));
        self::assertSame('#BlackLivesMatter', $this->container->invoke(Dummy::class, [[], '#BlackLivesMatter']));
        self::assertSame('#BlackLivesMatter', $this->container->invoke(Dummy::class, [['#BlackLivesMatter'], '%s']));
        self::assertSame(
            '#BlackLivesMatter',
            $this->container->invoke(Dummy::class, [
                'data' => [],
                'text' => '#BlackLivesMatter',
            ])
        );
        self::assertSame(
            '#BlackLivesMatter',
            $this->container->invoke(Dummy::class, [
                'data' => ['BlackLivesMatter'],
                'text' => '#%s',
            ])
        );
    }

    /**
     * @throws Throwable
     */
    public function testContainerProvideServiceProvider(): void
    {
        $this->container->provide(FoobarServiceProvider::class);

        self::assertTrue($this->container->has(Foo::class));
        self::assertTrue($this->container->has(Bar::class));
        self::assertTrue($this->container->has(Baz::class));
        self::assertInstanceOf(stdClass::class, $this->container->get(Foobar::class));
    }

    public function testContainerProvideThrowsServiceProviderAlreadyRegisteredException(): void
    {
        $this->expectException(Throwable::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceProviderAlreadyRegisteredException::class);
        $this->expectExceptionMessage(FoobarServiceProvider::class);

        $this->container->provide(FoobarServiceProvider::class);
        $this->container->provide(FoobarServiceProvider::class);
    }

    public function testContainerProvideThrowsServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException(): void
    {
        $this->expectException(Throwable::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceProviderMustBeAnInstanceOfServiceProviderInterfaceException::class);
        $this->expectExceptionMessage(ServiceProviderInterface::class);

        $this->container->provide(ServiceProviderInterface::class);
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterBind(): void
    {
        self::assertFalse($this->container->has(Dummy::class));
        self::assertFalse($this->container->has(DummyInterface::class));
        self::assertFalse($this->container->has(DummyFactory::class));

        $this->container->register(DummyInterface::class, Dummy::class);
        $this->container->register(DummyFactory::class);

        self::assertTrue($this->container->has(Dummy::class));
        self::assertTrue($this->container->has(DummyInterface::class));
        self::assertTrue($this->container->has(DummyFactory::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerRemove(): void
    {
        $this->container->provide(FoobarServiceProvider::class);

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
        $this->container->provide(FoobarServiceProvider::class);

        self::assertTrue($this->container->has(Foo::class));
        self::assertTrue($this->container->has(Bar::class));
        self::assertTrue($this->container->has(Baz::class));

        $foo = $this->container->get(Foo::class);
        $bar = $this->container->get(Bar::class);
        $baz = $this->container->get(Baz::class);

        $this->container->set(Foo::class, $this->container->build(Foo::class));
        $this->container->set(Bar::class, $this->container->build(Bar::class));
        $this->container->set(Baz::class, $this->container->build(Baz::class));

        self::assertInstanceOf(Foo::class, $this->container->get(Foo::class));
        self::assertInstanceOf(Bar::class, $this->container->get(Bar::class));
        self::assertInstanceOf(Baz::class, $this->container->get(Baz::class));

        self::assertNotSame($foo, $this->container->get(Foo::class));
        self::assertNotSame($bar, $this->container->get(Bar::class));
        self::assertNotSame($baz, $this->container->get(Baz::class));
    }

    public function testContainerSetClosure(): void
    {
        $object = new stdClass();

        $closure = static fn (ContainerInterface $container): stdClass => $object;

        $this->container->set(stdClass::class, $closure);

        self::assertSame($object, $this->container->get(stdClass::class));
    }

    public function testContainerSetObject(): void
    {
        $object = new stdClass();

        $this->container->set(stdClass::class, $object);

        self::assertSame($object, $this->container->get(stdClass::class));
    }

    public function testDestructContainerInterfaceAliasExists(): void
    {
        $this->container->__destruct();

        self::assertTrue($this->container->has(ContainerInterface::class));
    }

    public function testDontCloneException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(DontCloneContainerException::class);

        clone $this->container;
    }

    public function testDontSerializeException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(DontSerializeContainerException::class);

        $container = Container::getInstance();

        serialize($container);
    }

    public function testDontUnserializeException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(DontUnserializeContainerException::class);

        unserialize(
            // mocks a serialized Container::class
            sprintf('O:%s:"%s":0:{}', mb_strlen(Container::class), Container::class)
        );
    }

    public function testExtendThrowsServiceExtensionAlreadyRegisteredException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceExtensionAlreadyRegisteredException::class);

        $this->container->extend(stdClass::class, StdClassOneExtension::class);
        $this->container->extend(stdClass::class, StdClassOneExtension::class);
    }

    public function testExtendThrowsServiceExtensionMustBeAnInstanceOfExtensionInterfaceException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceExtensionMustBeAnInstanceOfExtensionInterfaceException::class);

        $this->container->extend(stdClass::class, stdClass::class);
    }

    public function testExtendThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->extend('', StdClassOneExtension::class);
    }
    public function testExtendThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->extend('   ', StdClassOneExtension::class);
    }

    public function testGetThrowsServiceNotFoundException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(ServiceNotFoundException::class);

        $this->container->get('does-not-exist');
    }

    public function testHasThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->has('');
    }

    public function testHasThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->has(' ');
    }

    public function testNotFoundExceptionImplementsContainerNotFoundExceptionInterface(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(ServiceNotFoundException::class);

        $this->container->get('not-found');
    }

    public function testNotInstantiableException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ClassNotInstantiableException::class);
        $this->expectExceptionMessage(Throwable::class);

        $this->container->build(Throwable::class);
    }

    public function testRegisterAbstractThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->register('', self::class);
    }
    public function testRegisterAbstractThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->register(' ', self::class);
    }

    public function testRegisterConcreteThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->register(self::class, '');
    }
    public function testRegisterConcreteThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->register(self::class, '  ');
    }

    public function testRegisterTag(): void
    {
        $this->container->tag(stdClass::class, ['first-tag']);

        $this->container->register(Foo::class, null, ['tag-2']);
        $this->container->register(stdClass::class, null, ['tag']);

        foreach ($this->container->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array($this->container->tagged('tag')));
    }


    public function testServiceMustBeNonEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceMustBeNonEmptyStringException::class);

        $this->container->alias('alias', '');
    }

    public function testServiceMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceMustBeNonEmptyStringException::class);

        $this->container->alias('alias', '        ');
    }


    public function testSetTag(): void
    {
        $this->container->set(stdClass::class, new stdClass(), ['tag']);

        foreach ($this->container->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array($this->container->tagged('tag')));
    }

    public function testSetThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->set('', new stdClass());
    }

    public function testSetThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->set('    ', new stdClass());
    }

    public function testTag(): void
    {
        $this->container->register(stdClass::class);

        $this->container->tag(stdClass::class, ['tag']);

        foreach ($this->container->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array($this->container->tagged('tag')));
    }

    public function testTagThrowsServiceNameMustBeNonEmptyStringException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->tag('', ['tag']);
    }
    public function testTagThrowsServiceNameMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceNameMustBeNonEmptyStringException::class);

        $this->container->tag('  ', ['tag']);
    }

    public function testTagThrowsServiceTagMustBeNonEmptyStringException(): void
    {
        $this->expectException(Throwable::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceTagMustBeNonEmptyStringException::class);

        $this->container->tag('service', ['']);
    }
    public function testTagThrowsServiceTagMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(Throwable::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceTagMustBeNonEmptyStringException::class);

        $this->container->tag('service', ['  ']);
    }

    public function testTagThrows(): void
    {
        $this->container->register(stdClass::class);

        $this->container->tag(stdClass::class, ['tag']);

        foreach ($this->container->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array($this->container->tagged('tag')));
    }

    public function testTaggedThrowsServiceTagNotFoundException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceTagNotFoundException::class);

        iterator_to_array($this->container->tagged('tag-not-found'));
    }

    public function testTaggedThrowsServiceTagMustBeNonEmptyStringException(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceTagMustBeNonEmptyStringException::class);

        iterator_to_array($this->container->tagged(''));
    }

    public function testTaggedThrowsServiceTagMustBeNonEmptyStringExceptionForEmptySpaces(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(ServiceTagMustBeNonEmptyStringException::class);

        iterator_to_array($this->container->tagged('  '));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws UnresolvableParameterException
     * @throws Throwable
     */
    public function testUnresolvableParameterExceptionBuild(): void
    {
        $this->expectException(Throwable::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(UnresolvableParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable class parameter "$number" in "%s::%s"; does not have a default value.',
            UnresolvableParameter::class,
            '__construct()'
        ));

        $this->container->build(UnresolvableParameter::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws UnresolvableParameterException
     * @throws Throwable
     */
    public function testUnresolvableParameterExceptionCall(): void
    {
        $this->expectException(Throwable::class);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectException(UnresolvableParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Unresolvable function parameter "%s" in "%s"; does not have a default value.',
            '$event',
            'Ghostwriter\Container\Tests\Fixture\typelessFunction()',
        ));

        $this->container->call('Ghostwriter\Container\Tests\Fixture\typelessFunction');
    }
}
