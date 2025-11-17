<?php

declare(strict_types=1);

namespace Tests\Unit;

use Generator;
use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ContainerExceptionInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Ghostwriter\Container\Service\Definition\ComposerExtraDefinition;
use Ghostwriter\Container\Service\Definition\ContainerDefinition;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface as PsrContainerInterface;
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
use Tests\Fixture\Extension\FoobarExtension;
use Tests\Fixture\Extension\StdClassOneExtension;
use Tests\Fixture\Extension\StdClassTwoExtension;
use Tests\Fixture\Factory\StdClassFactory;
use Tests\Fixture\Foo;
use Tests\Fixture\Foobar;
use Tests\Fixture\GitHub;
use Tests\Fixture\GitHubClient;
use Tests\Fixture\Definition\FoobarDefinition;
use Tests\Fixture\Definition\FoobarWithDependencyDefinition;
use Tests\Fixture\TestEvent;
use Tests\Fixture\TestEventListener;
use Tests\Fixture\UnionTypehintWithDefaultValue;
use Tests\Fixture\UnionTypehintWithoutDefaultValue;
use Throwable;
use function array_key_exists;
use function random_int;

#[CoversClass(ComposerExtraDefinition::class)]
#[CoversClass(ContainerDefinition::class)]
#[CoversClass(Container::class)]
#[CoversClassesThatImplementInterface(ContainerInterface::class)]
#[CoversClassesThatImplementInterface(ContainerExceptionInterface::class)]
#[CoversClassesThatImplementInterface(DefinitionInterface::class)]
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
        self::assertFalse($this->container->has(GitHub::class));

        $std = new GitHub($this->createMock(ClientInterface::class));

        $this->container->set(GitHub::class, $std);

        self::assertTrue($this->container->has(GitHub::class));

        self::assertFalse($this->container->has(PDO::class));

        $this->container->alias(GitHub::class, PDO::class);

        self::assertTrue($this->container->has(PDO::class));

        self::assertSame($std, $this->container->get(PDO::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerBind(): void
    {
        self::assertFalse($this->container->has(ClientInterface::class));
        self::assertFalse($this->container->has(GitHub::class));
        self::assertTrue($this->container->has(GitHubClient::class));

        // When GitHub::class asks for ClientInterface::class, resolve GitHubClient::class.
        $this->container->bind(GitHub::class, ClientInterface::class, GitHubClient::class);

        self::assertFalse($this->container->has(ClientInterface::class));
        self::assertTrue($this->container->has(GitHub::class));
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
        $instance = $this->container->build($class, $arguments);

        self::assertInstanceOf($class, $instance);

        if (! array_key_exists('value', $arguments)) {
            return;
        }

        self::assertSame($arguments['value'], $instance->value());
    }

    /**
     * @throws Throwable
     */
    public function testContainerBuildDefinitionDoesNotRegisterDefinition(): void
    {
        $foobarDefinition = $this->container->build(FoobarDefinition::class);
        self::assertInstanceOf(FoobarDefinition::class, $foobarDefinition);

        $second = $this->container->build(FoobarDefinition::class);
        self::assertInstanceOf(FoobarDefinition::class, $second);

        self::assertNotSame($foobarDefinition, $second);
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

        self::assertEmpty($testEvent->all());

        while ($actual1--) {
            $this->container->call($callback, [$testEvent]);
        }

        self::assertCount($expectedCount, $testEvent->all());

        while ($actual2--) {
            $this->container->call($callback, [$testEvent]);
        }

        self::assertCount($expectedCount * 2, $testEvent->all());

        $this->container->unset(TestEvent::class);
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
        self::assertSame('Untitled Text', $this->container->call(Dummy::class));
        self::assertSame('#BlackLivesMatter', $this->container->call(Dummy::class, [[], '#BlackLivesMatter']));
        self::assertSame('#BlackLivesMatter', $this->container->call(Dummy::class, [['#BlackLivesMatter'], '%s']));
        self::assertSame('#BlackLivesMatter', $this->container->call(Dummy::class, [
            'data' => [],
            'text' => '#BlackLivesMatter',
        ]));
        self::assertSame('#BlackLivesMatter', $this->container->call(Dummy::class, [
            'data' => ['BlackLivesMatter'],
            'text' => '#%s',
        ]));
    }

    /**
     * @throws Throwable
     */
    public function testContainerProvideDefinition(): void
    {
        $this->container->define(FoobarDefinition::class);

        self::assertTrue($this->container->has(Foo::class));
        self::assertTrue($this->container->has(Bar::class));
        self::assertTrue($this->container->has(Baz::class));
        self::assertInstanceOf(stdClass::class, $this->container->get(Foobar::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerResetClass(): void
    {
        $instance = new stdClass();
        $this->container->set(stdClass::class, $instance);

        self::assertTrue($this->container->has(stdClass::class));
        self::assertSame($instance, $this->container->get(stdClass::class));

        $this->container->reset();

        self::assertTrue($this->container->has(stdClass::class));
        self::assertNotSame($instance, $this->container->get(stdClass::class));
    }

    /**
     * @throws Throwable
     */
    public function testContainerRemove(): void
    {
        $this->container->define(FoobarDefinition::class);

        self::assertTrue($this->container->has(Foo::class));
        $foo = $this->container->get(Foo::class);
        self::assertSame($foo, $this->container->get(Foo::class));

        self::assertTrue($this->container->has(Bar::class));
        $bar = $this->container->get(Bar::class);
        self::assertSame($bar, $this->container->get(Bar::class));

        self::assertTrue($this->container->has(Baz::class));

        $baz = $this->container->get(Baz::class);
        self::assertSame($baz, $this->container->get(Baz::class));

        $this->container->unset(Foo::class);
        $this->container->unset(Bar::class);
        $this->container->unset(Baz::class);

        self::assertTrue($this->container->has(Foo::class));
        $fooAfterUnset = $this->container->get(Foo::class);
        self::assertNotSame($foo, $fooAfterUnset);

        self::assertTrue($this->container->has(Bar::class));
        $barAfterUnset = $this->container->get(Bar::class);
        self::assertNotSame($bar, $barAfterUnset);

        self::assertTrue($this->container->has(Baz::class));
        $bazAfterUnset = $this->container->get(Baz::class);
        self::assertNotSame($baz, $bazAfterUnset);
    }

    /**
     * @throws Throwable
     */
    public function testContainerReset(): void
    {
        $this->container->define(FoobarDefinition::class);

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
        $this->container->reset();

        self::assertTrue($this->container->has(ContainerInterface::class));
    }

    /**
     * @template TService of object
     *
     * @throws Throwable
     */
    public function testGhostwriterContainerCanInstantiatePsrContainer(): void
    {
        self::assertInstanceOf(Container::class, $this->container->get(PsrContainerInterface::class));
    }

    public function testImplementsPsrContainerInterface(): void
    {
        self::assertInstanceOf(PsrContainerInterface::class, Container::getInstance());
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
            static function (TestEvent $testEvent): void {
                $testEvent->collect($testEvent::class);
            },
        ];
    }

    /**
     * @return Generator<class-string<ArrayConstructor|Bar|Baz|BoolConstructor|CallableConstructor|EmptyConstructor|FloatConstructor|Foo|FoobarExtension|FoobarDefinition|FoobarWithDependencyDefinition|IntConstructor|IterableConstructor|MixedConstructor|ObjectConstructor|OptionalConstructor|self|StringConstructor|TypelessConstructor|UnionTypehintWithDefaultValue|UnionTypehintWithoutDefaultValue>,array>
     */
    public static function dataProviderServiceClasses(): Generator
    {
        yield ContainerInterface::class => [ContainerInterface::class, []];
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
        yield FoobarWithDependencyDefinition::class => [FoobarWithDependencyDefinition::class];
        yield FoobarDefinition::class => [FoobarDefinition::class];
        yield FoobarExtension::class => [FoobarExtension::class];
        yield self::class => [self::class, ['name']];
    }
}
