<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Tests\Unit;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Instantiator;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\ParameterBuilder;
use Ghostwriter\Container\Reflector;
use Ghostwriter\Container\Tests\Fixture\Bar;
use Ghostwriter\Container\Tests\Fixture\Baz;
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
use Ghostwriter\Container\Tests\Fixture\NonStdClassFactory;
use Ghostwriter\Container\Tests\Fixture\ServiceProvider\FoobarServiceProvider;
use Ghostwriter\Container\Tests\Fixture\ServiceProvider\FoobarWithDependencyServiceProvider;
use Ghostwriter\Container\Tests\Fixture\StdClassFactory;
use Ghostwriter\Container\Tests\Fixture\TestEvent;
use Ghostwriter\Container\Tests\Fixture\TestEventListener;
use Ghostwriter\Container\Tests\Fixture\UnionTypehintWithDefaultValue;
use Ghostwriter\Container\Tests\Fixture\UnionTypehintWithoutDefaultValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Throwable;
use function array_key_exists;

#[CoversClass(Container::class)]
#[CoversClass(Instantiator::class)]
#[CoversClass(ParameterBuilder::class)]
#[CoversClass(Reflector::class)]
final class ContainerTest extends AbstractTestCase
{
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
                'value' => static fn(ContainerInterface $container): null => null,
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
     * @throws Throwable
     */
    public function testContainerAlias(): void
    {
        self::assertFalse(Container::getInstance()->has(stdClass::class));

        $std = new stdClass();

        Container::getInstance()->set(stdClass::class, $std);

        self::assertTrue(Container::getInstance()->has(stdClass::class));

        self::assertFalse(Container::getInstance()->has('class'));

        Container::getInstance()->alias('class', stdClass::class);

        self::assertTrue(Container::getInstance()->has('class'));

        self::assertSame($std, Container::getInstance()->get('class'));
    }

    /**
     * @throws Throwable
     */
    public function testContainerBind(): void
    {
        self::assertFalse(Container::getInstance()->has(GitHub::class));
        self::assertFalse(Container::getInstance()->has(ClientInterface::class));
        self::assertFalse(Container::getInstance()->has(GitHubClient::class));

        // When GitHub::class asks for ClientInterface::class, resolve GitHubClient::class.
        Container::getInstance()->bind(
            GitHub::class,
            ClientInterface::class,
            GitHubClient::class
        );

        self::assertTrue(
            Container::getInstance()->has(GitHubClient::class)
        );

        self::assertInstanceOf(GitHub::class, Container::getInstance()->get(GitHub::class));

        self::assertInstanceOf(ClientInterface::class, Container::getInstance()->get(GitHub::class)->getClient());

        self::assertTrue(Container::getInstance()->has(GitHubClient::class));
        self::assertTrue(Container::getInstance()->has(GitHub::class));
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @throws Throwable
     */
    #[DataProvider('dataProviderServiceClasses')]
    public function testContainerBuild(string $class, array $arguments = []): void
    {
        $buildService = Container::getInstance()->build($class, $arguments);

        $getService = Container::getInstance()->get($class);

        self::assertSame($buildService, $getService);

        if (!array_key_exists('value', $arguments)) {
            return;
        }

        self::assertSame($arguments['value'], Container::getInstance()->get($class)->value());
    }

    /**
     * @throws Throwable
     */
    public function testContainerBuildServiceProviderDoesNotRegisterServiceProvider(): void
    {
        $foobarServiceProvider = Container::getInstance()->build(FoobarServiceProvider::class);
        self::assertInstanceOf(FoobarServiceProvider::class, $foobarServiceProvider);

        $second = Container::getInstance()->build(FoobarServiceProvider::class);
        self::assertInstanceOf(FoobarServiceProvider::class, $second);

        self::assertNotSame($foobarServiceProvider, $second);

        self::assertFalse(Container::getInstance()->has(Foo::class));
        self::assertFalse(Container::getInstance()->has(Bar::class));
        self::assertFalse(Container::getInstance()->has(Baz::class));

        Container::getInstance()->provide(FoobarServiceProvider::class);

        self::assertTrue(Container::getInstance()->has(Foo::class));
        self::assertTrue(Container::getInstance()->has(Bar::class));
        self::assertTrue(Container::getInstance()->has(Baz::class));
    }

    /**
     * @param callable():void $callback
     *
     * @throws Throwable
     */
    #[DataProvider('dataProviderContainerCallables')]
    public function testContainerCall(callable $callback): void
    {
        $testEvent = Container::getInstance()->get(TestEvent::class);

        self::assertSame([], $testEvent->all());
        $expectedCount = random_int(10, 50);
        $actual1 = $expectedCount;
        $actual2 = $expectedCount;

        while ($actual1--) {
            Container::getInstance()->call($callback, [$testEvent]);
        }

        self::assertCount($expectedCount, $testEvent->all());

        while ($actual2--) {
            Container::getInstance()->call($callback, [$testEvent]);
        }

        self::assertCount($expectedCount * 2, $testEvent->all());

        Container::getInstance()->remove(TestEvent::class);
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
        Container::getInstance()->set(stdClass::class, static fn(): stdClass => new stdClass());

        self::assertTrue(Container::getInstance()->has(stdClass::class));

        Container::getInstance()->__destruct();

        self::assertFalse(Container::getInstance()->has(stdClass::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerExtend(): void
    {
        Container::getInstance()->extend(
            stdClass::class,
            StdClassOneExtension::class
        );

        Container::getInstance()->extend(
            stdClass::class,
            StdClassTwoExtension::class
        );

        self::assertInstanceOf(
            stdClass::class,
            Container::getInstance()->get(stdClass::class)
        );

        self::assertInstanceOf(
            stdClass::class,
            Container::getInstance()->get(stdClass::class)->one
        );

        self::assertInstanceOf(
            stdClass::class,
            Container::getInstance()->get(stdClass::class)->two
        );
    }

    /**
     * @throws Throwable
     */
    public function testContainerImplementsContainerInterface(): void
    {
        $container = Container::getInstance();

        self::assertInstanceOf(ContainerInterface::class, $container);
        self::assertInstanceOf(Container::class, $container);
    }

    /**
     * @throws Throwable
     */
    public function testContainerInvokeDefaultValueAvailable(): void
    {
        self::assertSame('Untitled Text', Container::getInstance()->invoke(Dummy::class));
        self::assertSame('#BlackLivesMatter', Container::getInstance()->invoke(Dummy::class, [[], '#BlackLivesMatter']));
        self::assertSame('#BlackLivesMatter', Container::getInstance()->invoke(Dummy::class, [['#BlackLivesMatter'], '%s']));
        self::assertSame(
            '#BlackLivesMatter',
            Container::getInstance()->invoke(Dummy::class, [
                'data' => [],
                'text' => '#BlackLivesMatter',
            ])
        );
        self::assertSame(
            '#BlackLivesMatter',
            Container::getInstance()->invoke(Dummy::class, [
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
        Container::getInstance()->provide(FoobarServiceProvider::class);

        self::assertTrue(Container::getInstance()->has(Foo::class));
        self::assertTrue(Container::getInstance()->has(Bar::class));
        self::assertTrue(Container::getInstance()->has(Baz::class));
        self::assertInstanceOf(stdClass::class, Container::getInstance()->get(Foobar::class));
    }


    /**
     * @throws Throwable
     */
    public function testContainerRegisterBind(): void
    {
        self::assertFalse(Container::getInstance()->has(Dummy::class));
        self::assertFalse(Container::getInstance()->has(DummyInterface::class));
        self::assertFalse(Container::getInstance()->has(DummyFactory::class));

        Container::getInstance()->register(DummyInterface::class, Dummy::class);
        Container::getInstance()->register(DummyFactory::class);

        self::assertTrue(Container::getInstance()->has(Dummy::class));
        self::assertTrue(Container::getInstance()->has(DummyInterface::class));
        self::assertTrue(Container::getInstance()->has(DummyFactory::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerRemove(): void
    {
        Container::getInstance()->provide(FoobarServiceProvider::class);

        self::assertTrue(Container::getInstance()->has(Foo::class));
        self::assertTrue(Container::getInstance()->has(Bar::class));
        self::assertTrue(Container::getInstance()->has(Baz::class));

        Container::getInstance()->remove(Foo::class);
        Container::getInstance()->remove(Bar::class);
        Container::getInstance()->remove(Baz::class);

        self::assertFalse(Container::getInstance()->has(Foo::class));
        self::assertFalse(Container::getInstance()->has(Bar::class));
        self::assertFalse(Container::getInstance()->has(Baz::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerReset(): void
    {
        Container::getInstance()->provide(FoobarServiceProvider::class);

        self::assertTrue(Container::getInstance()->has(Foo::class));
        self::assertTrue(Container::getInstance()->has(Bar::class));
        self::assertTrue(Container::getInstance()->has(Baz::class));

        $foo = Container::getInstance()->get(Foo::class);
        $bar = Container::getInstance()->get(Bar::class);
        $baz = Container::getInstance()->get(Baz::class);

        Container::getInstance()->set(Foo::class, Container::getInstance()->build(Foo::class));
        Container::getInstance()->set(Bar::class, Container::getInstance()->build(Bar::class));
        Container::getInstance()->set(Baz::class, Container::getInstance()->build(Baz::class));

        self::assertInstanceOf(Foo::class, Container::getInstance()->get(Foo::class));
        self::assertInstanceOf(Bar::class, Container::getInstance()->get(Bar::class));
        self::assertInstanceOf(Baz::class, Container::getInstance()->get(Baz::class));

        self::assertNotSame($foo, Container::getInstance()->get(Foo::class));
        self::assertNotSame($bar, Container::getInstance()->get(Bar::class));
        self::assertNotSame($baz, Container::getInstance()->get(Baz::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerSetClosure(): void
    {
        $object = new stdClass();

        $closure = static fn(ContainerInterface $container): stdClass => $object;

        Container::getInstance()->set(stdClass::class, $closure);

        self::assertSame($object, Container::getInstance()->get(stdClass::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerSetObject(): void
    {
        $object = new stdClass();

        Container::getInstance()->set(stdClass::class, $object);

        self::assertSame($object, Container::getInstance()->get(stdClass::class));
    }

    public function testDestructContainerInterfaceAliasExists(): void
    {
        Container::getInstance()->__destruct();

        self::assertTrue(Container::getInstance()->has(ContainerInterface::class));
    }


    /**
     * @throws Throwable
     */
    public function testRegisterTag(): void
    {
        Container::getInstance()->tag(stdClass::class, ['first-tag']);

        Container::getInstance()->tag(Foo::class,['tag-2']);
        Container::getInstance()->tag(stdClass::class, ['tag']);

        foreach (Container::getInstance()->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        Container::getInstance()->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array(Container::getInstance()->tagged('tag')));
    }


    /**
     * @throws Throwable
     */
    public function testSetTag(): void
    {
        Container::getInstance()->set(stdClass::class, new stdClass(), ['tag']);

        foreach (Container::getInstance()->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        Container::getInstance()->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array(Container::getInstance()->tagged('tag')));
    }


    /**
     * @throws Throwable
     */
    public function testTag(): void
    {
        Container::getInstance()->tag(stdClass::class, ['tag']);

        foreach (Container::getInstance()->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        Container::getInstance()->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array(Container::getInstance()->tagged('tag')));
    }


    /**
     * @throws Throwable
     */
    public function testTagThrows(): void
    {
        Container::getInstance()->tag(stdClass::class, ['tag']);

        foreach (Container::getInstance()->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        Container::getInstance()->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array(Container::getInstance()->tagged('tag')));
    }

    /**
     * @throws Throwable
     */
    public function testFactory(): void
    {
        Container::getInstance()->factory(stdClass::class, StdClassFactory::class);

        self::assertSame(
            '#FreePalestine',
            Container::getInstance()->get(stdClass::class)->blackLivesMatter
        );
    }
}
