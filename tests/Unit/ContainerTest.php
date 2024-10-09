<?php

declare(strict_types=1);

namespace Tests\Unit;

use Generator;
use Ghostwriter\Container\Attribute\Extension;
use Ghostwriter\Container\Attribute\Factory;
use Ghostwriter\Container\Attribute\Inject;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\List\Aliases;
use Ghostwriter\Container\List\Bindings;
use Ghostwriter\Container\List\Builders;
use Ghostwriter\Container\List\Dependencies;
use Ghostwriter\Container\List\Extensions;
use Ghostwriter\Container\List\Factories;
use Ghostwriter\Container\List\Instances;
use Ghostwriter\Container\List\Providers;
use Ghostwriter\Container\List\Tags;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionParameter;
use stdClass;
use Tests\Fixture\Bar;
use Tests\Fixture\Baz;
use Tests\Fixture\ClassWithArray;
use Tests\Fixture\ClientInterface;
use Tests\Fixture\Constructor\ArrayConstructor;
use Tests\Fixture\Constructor\BoolConstructor;
use Tests\Fixture\Constructor\CallableConstructor;
use Tests\Fixture\Constructor\EmptyConstructor;
use Tests\Fixture\Constructor\FloatConstructor;
use Tests\Fixture\Constructor\IntConstructor;
use Tests\Fixture\Constructor\IterableConstructor;
use Tests\Fixture\Constructor\MixedConstructor;
use Tests\Fixture\Constructor\ObjectConstructor;
use Tests\Fixture\Constructor\OptionalConstructor;
use Tests\Fixture\Constructor\StringConstructor;
use Tests\Fixture\Constructor\TypelessConstructor;
use Tests\Fixture\Dummy;
use Tests\Fixture\DummyInterface;
use Tests\Fixture\Extension\FoobarExtension;
use Tests\Fixture\Extension\StdClassOneExtension;
use Tests\Fixture\Extension\StdClassTwoExtension;
use Tests\Fixture\Factory\DummyFactory;
use Tests\Fixture\Factory\StdClassFactory;
use Tests\Fixture\Foo;
use Tests\Fixture\Foobar;
use Tests\Fixture\GitHub;
use Tests\Fixture\GitHubClient;
use Tests\Fixture\ServiceProvider\FoobarServiceProvider;
use Tests\Fixture\ServiceProvider\FoobarWithDependencyServiceProvider;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventListener;
use Tests\Fixture\UnionTypehintWithDefaultValue;
use Tests\Fixture\UnionTypehintWithoutDefaultValue;
use Throwable;

/**
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress UndefinedClass
 * @psalm-suppress UnevaluatedCode
 */
#[CoversClass(Aliases::class)]
#[CoversClass(Bindings::class)]
#[CoversClass(Builders::class)]
#[CoversClass(Container::class)]
#[CoversClass(Dependencies::class)]
#[CoversClass(Extension::class)]
#[CoversClass(Extensions::class)]
#[CoversClass(Factories::class)]
#[CoversClass(Factory::class)]
#[CoversClass(Inject::class)]
#[CoversClass(Instances::class)]
#[CoversClass(Providers::class)]
#[CoversClass(Tags::class)]
final class ContainerTest extends AbstractTestCase
{
    /**
     * @throws Throwable
     */
    public function testBuildParamPosition(): void
    {
        $classWithArray = $this->container->build(ClassWithArray::class, [
            'items' => ['tag'],
        ]);

        self::assertInstanceOf(ClassWithArray::class, $classWithArray);
    }

    /**
     * @throws Throwable
     */
    public function testBuildResolvesAlias(): void
    {
        $this->container->alias(GitHubClient::class, ClientInterface::class);

        self::assertInstanceOf(GitHubClient::class, $this->container->build(ClientInterface::class));
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

        $this->container->alias(stdClass::class, 'class');

        self::assertTrue($this->container->has('class'));

        self::assertSame($std, $this->container->get('class'));
    }

    /**
     * @throws Throwable
     */
    public function testContainerBind(): void
    {
        self::assertFalse($this->container->has(GitHub::class));
        self::assertFalse($this->container->has(ClientInterface::class));
        self::assertFalse($this->container->has(GitHubClient::class));

        // When GitHub::class asks for ClientInterface::class, resolve GitHubClient::class.
        $this->container->bind(GitHub::class, ClientInterface::class, GitHubClient::class);

        self::assertTrue($this->container->has(GitHubClient::class));

        self::assertInstanceOf(GitHub::class, $this->container->get(GitHub::class));

        self::assertInstanceOf(ClientInterface::class, $this->container->get(GitHub::class)->getClient());

        self::assertTrue($this->container->has(GitHubClient::class));
        self::assertTrue($this->container->has(GitHub::class));
    }

    /**
     * @param class-string<ArrayConstructor|BoolConstructor|CallableConstructor|EmptyConstructor|FloatConstructor|IntConstructor|IterableConstructor|MixedConstructor|ObjectConstructor|OptionalConstructor|StringConstructor|TypelessConstructor|UnionTypehintWithDefaultValue|UnionTypehintWithoutDefaultValue> $class
     * @param array<string, mixed>                                                                                                                                                                                                                                                                                $arguments
     *
     * @throws Throwable
     */
    #[DataProvider('dataProviderServiceClasses')]
    public function testContainerBuild(string $class, array $arguments = []): void
    {
        $buildService = $this->container->build($class, $arguments);

        $getService = $this->container->get($class);

        self::assertSame($buildService, $getService);

        if (! \array_key_exists('value', $arguments)) {
            return;
        }

        self::assertTrue(\class_exists($class));

        self::assertSame($arguments['value'], $this->container->get($class)->value());
    }

    /**
     * @throws Throwable
     */
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
        $expectedCount = \random_int(10, 50);
        $actual1 = $expectedCount;
        $actual2 = $expectedCount;

        self::assertCount(0, $testEvent->all());

        while ($actual1--) {
            $this->container->call($callback, [$testEvent]);
        }

        self::assertCount($expectedCount, $testEvent->all());

        while ($actual2--) {
            $this->container->call($callback, [$testEvent]);
        }

        self::assertCount($expectedCount * 2, $testEvent->all());

        $this->container->tag(TestEvent::class, ['tag']);

        $this->container->remove(TestEvent::class);
    }

    public function testContainerConstruct(): void
    {
        self::assertSame($this->container, $this->container);
    }

    /**
     * @throws Throwable
     */
    public function testContainerExtend(): void
    {
        $this->container->extend(stdClass::class, StdClassOneExtension::class);

        $this->container->extend(stdClass::class, StdClassTwoExtension::class);

        self::assertInstanceOf(stdClass::class, $this->container->get(stdClass::class));

        self::assertInstanceOf(stdClass::class, $this->container->get(stdClass::class)->one);

        self::assertInstanceOf(stdClass::class, $this->container->get(stdClass::class)->two);
    }

    /**
     * @throws Throwable
     */
    public function testContainerExtendFactory(): void
    {
        $this->container->factory(stdClass::class, StdClassFactory::class);
        $this->container->extend(stdClass::class, StdClassOneExtension::class);

        $this->container->extend(stdClass::class, StdClassTwoExtension::class);

        self::assertInstanceOf(stdClass::class, $this->container->get(stdClass::class));

        self::assertInstanceOf(stdClass::class, $this->container->get(stdClass::class)->one);

        self::assertInstanceOf(stdClass::class, $this->container->get(stdClass::class)->two);

        self::assertSame('#FreePalestine', $this->container->get(stdClass::class)->blackLivesMatter);

        self::assertSame($this->container->get(stdClass::class), $this->container->get(stdClass::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerImplementsContainerInterface(): void
    {
        $container = $this->container;

        self::assertInstanceOf(ContainerInterface::class, $container);
        self::assertInstanceOf(Container::class, $container);
    }

    /**
     * @throws Throwable
     */
    public function testContainerInvokeDefaultValueAvailable(): void
    {
        self::assertSame('Untitled Text', $this->container->invoke(Dummy::class));
        self::assertSame('#BlackLivesMatter', $this->container->invoke(Dummy::class, [[], '#BlackLivesMatter']));
        self::assertSame('#BlackLivesMatter', $this->container->invoke(Dummy::class, [['#BlackLivesMatter'], '%s']));
        self::assertSame('#BlackLivesMatter', $this->container->invoke(Dummy::class, [
            'data' => [],
            'text' => '#BlackLivesMatter',
        ]));
        self::assertSame('#BlackLivesMatter', $this->container->invoke(Dummy::class, [
            'data' => ['BlackLivesMatter'],
            'text' => '#%s',
        ]));
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

    /**
     * @throws Throwable
     */
    public function testContainerPurge(): void
    {
        $this->container->set(stdClass::class, static fn (): stdClass => new stdClass());

        self::assertTrue($this->container->has(stdClass::class));

        $this->container->purge();

        self::assertFalse($this->container->has(stdClass::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerRegisterBind(): void
    {
        self::assertFalse($this->container->has(Dummy::class));
        self::assertFalse($this->container->has(DummyInterface::class));
        self::assertFalse($this->container->has(DummyFactory::class));

        $this->container->register(DummyInterface::class, Dummy::class, [DummyInterface::class]);
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

    /**
     * @throws Throwable
     */
    public function testContainerSetAndGetFactory(): void
    {
        $this->container->set(
            UnionTypehintWithoutDefaultValue::class,
            static fn (
                ContainerInterface $container
            ): UnionTypehintWithoutDefaultValue => $container->build(UnionTypehintWithoutDefaultValue::class, [1])
        );
        self::assertInstanceOf(
            UnionTypehintWithoutDefaultValue::class,
            $this->container->get(UnionTypehintWithoutDefaultValue::class)
        );
    }

    /**
     * @throws Throwable
     */
    public function testContainerSetClosure(): void
    {
        $object = new stdClass();

        $closure = static fn (ContainerInterface $container): stdClass => $object;

        $this->container->set(stdClass::class, $closure);

        self::assertSame($object, $this->container->get(stdClass::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerSetObject(): void
    {
        $object = new stdClass();

        $this->container->set(stdClass::class, $object);

        self::assertSame($object, $this->container->get(stdClass::class));
    }

    /**
     * @throws Throwable
     */
    public function testFactory(): void
    {
        $this->container->factory(stdClass::class, StdClassFactory::class);

        self::assertSame('#FreePalestine', $this->container->get(stdClass::class)->blackLivesMatter);
    }

    public function testPurgeContainerInterfaceAliasExists(): void
    {
        $this->container->purge();

        self::assertTrue($this->container->has(ContainerInterface::class));
    }

    /**
     * @template TService of object
     *
     * @throws Throwable
     */
    public function testRegisterTag(): void
    {
        $this->container->tag(stdClass::class, ['first-tag']);

        $this->container->tag(Foo::class, ['tag-2']);
        $this->container->tag(stdClass::class, ['tag']);

        self::assertContainsOnlyInstancesOf(stdClass::class, $this->container->tagged('tag'));

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, \iterator_to_array($this->container->tagged('tag')));
    }

    /**
     * @template TService of object
     *
     * @throws Throwable
     */
    public function testSetTag(): void
    {
        $this->container->set(stdClass::class, new stdClass(), [stdClass::class]);

        self::assertContainsOnlyInstancesOf(stdClass::class, $this->container->tagged(stdClass::class));

        $this->container->untag(stdClass::class, [stdClass::class]);

        self::assertCount(0, \iterator_to_array($this->container->tagged(stdClass::class)));
    }

    /**
     * @template TService of object
     *
     * @throws Throwable
     */
    public function testTag(): void
    {
        $this->container->tag(stdClass::class, ['tag']);

        self::assertContainsOnlyInstancesOf(stdClass::class, $this->container->tagged('tag'));

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, \iterator_to_array($this->container->tagged('tag')));
    }

    /**
     * @template TService of object
     *
     * @throws Throwable
     */
    public function testTagThrows(): void
    {
        $this->container->tag(stdClass::class, ['tag']);

        self::assertContainsOnlyInstancesOf(stdClass::class, $this->container->tagged('tag'));

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, \iterator_to_array($this->container->tagged('tag')));
    }

    /**
     * @throws Throwable
     */
    public static function buildParametersDataProvider(): Generator
    {
        $stdClass = new stdClass();

        $closure = static fn (stdClass $_): stdClass => $stdClass;

        $empty = [];

        yield from [
            'no parameters & no arguments' => [$empty, $empty, $empty],

            'no parameters & arguments' => [$empty, [
                'foo' => $stdClass,
            ], $empty],

            'parameters & no arguments' => [[new ReflectionParameter($closure, 'foo')], $empty, [$stdClass]],
        ];
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
        yield 'FunctionCall@typedFunction' => ['Tests\Fixture\typedFunction'];
        yield 'FunctionCall@typelessFunction' => ['Tests\Fixture\typelessFunction'];
        yield 'Invokable' => [new TestEventListener()];
        yield 'StaticMethodCall' => [TestEventListener::class . '::onStatic'];
        yield 'TypelessAnonymousFunctionCall' => [
            /**
             * @param TestEvent $event
             */
            static function ($event): void {
                $event->collect($event::class);
            },
        ];
    }

    /**
     * @return Generator<class-string<ArrayConstructor|Bar|Baz|BoolConstructor|CallableConstructor|EmptyConstructor|FloatConstructor|Foo|FoobarExtension|FoobarServiceProvider|FoobarWithDependencyServiceProvider|IntConstructor|IterableConstructor|MixedConstructor|ObjectConstructor|OptionalConstructor|self|StringConstructor|TypelessConstructor|UnionTypehintWithDefaultValue|UnionTypehintWithoutDefaultValue>,array>
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
        yield FoobarWithDependencyServiceProvider::class => [FoobarWithDependencyServiceProvider::class];
        yield FoobarServiceProvider::class => [FoobarServiceProvider::class];
        yield FoobarExtension::class => [FoobarExtension::class];
        yield self::class => [self::class, ['name']];
    }
}
