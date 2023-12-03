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
        self::assertFalse($this->container->has(stdClass::class));

        $std = new stdClass();

        $this->container->set(stdClass::class, $std);

        self::assertTrue($this->container->has(stdClass::class));

        self::assertFalse($this->container->has('class'));

        $this->container->alias('class', stdClass::class);

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

        if (!array_key_exists('value', $arguments)) {
            return;
        }

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
        self::assertSame($this->container, $this->container);
    }

    /**
     * @throws Throwable
     */
    public function testContainerDestruct(): void
    {
        $this->container->set(stdClass::class, static fn(): stdClass => new stdClass());

        self::assertTrue($this->container->has(stdClass::class));

        $this->container->__destruct();

        self::assertFalse($this->container->has(stdClass::class));
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

    /**
     * @throws Throwable
     */
    public function testContainerSetClosure(): void
    {
        $object = new stdClass();

        $closure = static fn(ContainerInterface $container): stdClass => $object;

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

    public function testDestructContainerInterfaceAliasExists(): void
    {
        $this->container->__destruct();

        self::assertTrue($this->container->has(ContainerInterface::class));
    }


    /**
     * @throws Throwable
     */
    public function testRegisterTag(): void
    {
        $this->container->tag(stdClass::class, ['first-tag']);

        $this->container->tag(Foo::class,['tag-2']);
        $this->container->tag(stdClass::class, ['tag']);

        foreach ($this->container->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array($this->container->tagged('tag')));
    }


    /**
     * @throws Throwable
     */
    public function testSetTag(): void
    {
        $this->container->set(stdClass::class, new stdClass(), ['tag']);

        foreach ($this->container->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array($this->container->tagged('tag')));
    }


    /**
     * @throws Throwable
     */
    public function testTag(): void
    {
        $this->container->tag(stdClass::class, ['tag']);

        foreach ($this->container->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array($this->container->tagged('tag')));
    }


    /**
     * @throws Throwable
     */
    public function testTagThrows(): void
    {
        $this->container->tag(stdClass::class, ['tag']);

        foreach ($this->container->tagged('tag') as $service) {
            self::assertInstanceOf(stdClass::class, $service);
        }

        $this->container->untag(stdClass::class, ['tag']);

        self::assertCount(0, iterator_to_array($this->container->tagged('tag')));
    }

    /**
     * @throws Throwable
     */
    public function testFactory(): void
    {
        $this->container->factory(stdClass::class, StdClassFactory::class);

        self::assertSame(
            '#FreePalestine',
            $this->container->get(stdClass::class)->blackLivesMatter
        );
    }

    /**
     * @throws Throwable
     */
    public function testBuildResolvesAlias(): void
    {
        $this->container->alias(ClientInterface::class, GitHubClient::class);

        self::assertInstanceOf(
            GitHubClient::class,
            $this->container->build(ClientInterface::class)
        );
    }
}
