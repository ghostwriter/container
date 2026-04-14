# Container

[![GitHub Sponsors](https://img.shields.io/github/sponsors/ghostwriter?label=Sponsor+@ghostwriter/container&logo=GitHub+Sponsors)](https://github.com/sponsors/ghostwriter)
[![Automation](https://github.com/ghostwriter/container/actions/workflows/automation.yml/badge.svg)](https://github.com/ghostwriter/container/actions/workflows/automation.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/container?color=8892bf)](https://www.php.net/supported-versions)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/container?color=blue)](https://packagist.org/packages/ghostwriter/container)

Provides an extensible Dependency Injection Service Container for Automated Object Composition, Interception, and Lifetime Management.

It supports autowiring, singleton-style service retrieval, contextual bindings, service factories, post-construction extensions, provider-based registration, and PSR container interoperability.

## Why use it?

- Auto-discover providers from Composer metadata
- Autowire concrete classes through reflection
- Cache shared services with `get()`
- Create fresh instances with `build()`
- Create services through factories
- Decorate services with extensions
- Override constructor or callable arguments by name or position
- PSR-compatible wrapper
- Register aliases for interfaces and abstractions
- Swap implementations contextually with `bind()`

## Installation

Install the package with Composer:

```bash
composer require ghostwriter/container
```

## Usage

`get()` resolves a service once and returns the same instance on subsequent calls.

```php
<?php

declare(strict_types=1);

use Ghostwriter\Container\Container;

final readonly class Dependency
{
}

final readonly class Service
{
    public function __construct(
        private Dependency $dependency,
    ) {}

    public function dependency(): Dependency
    {
        return $this->dependency;
    }
}

$container = Container::getInstance();

$service = $container->get(Service::class);

assert($service instanceof Service);
assert($service->dependency() instanceof Dependency);
assert($service === $container->get(Service::class));
```

## Core concepts

### `get()` vs `build()`

- `get(Foo::class)` returns the same resolved instance each time.
- `build(Foo::class)` creates a new instance every time.

```php
$shared = $container->get(Service::class);
$sameShared = $container->get(Service::class);

assert($shared === $sameShared);

$fresh = $container->build(Service::class);

assert($fresh instanceof Service);
assert($fresh !== $shared);
```

### Constructor argument overrides

You can override constructor parameters by name or by position when building a service.

```php
final readonly class Report
{
    public function __construct(
        private string $title,
        private int $pageCount = 1,
    ) {}
}

$report = $container->build(Report::class, [
    'title' => 'Architecture Notes',
    'pageCount' => 42,
]);

$otherReport = $container->build(Report::class, [
    0 => 'Release Notes',
    1 => 5,
]);
```

### Invoking callables with `call()`

`call()` resolves object dependencies for closures, invokable classes, callable arrays, static method strings, and function names.

```php
use Ghostwriter\Container\Container;

$message = $container->call(
    static function (Dependency $dependency, string $name): string {
        return $name . ' is ready';
    },
    ['name' => 'container']
);

assert($message === 'container is ready');
```

### Checking availability with `has()`

`has()` answers whether the container can resolve a service. It does not instantiate the service, run factories, or execute extensions.

```php
assert($container->has(Service::class) === true);
```

## Aliases

Use `alias()` to map an interface or alternative service id to a concrete implementation.

```php
interface ClientInterface
{
}

final readonly class RestClient implements ClientInterface
{
}

$container->alias(ClientInterface::class, RestClient::class);

$client = $container->get(ClientInterface::class);

assert($client instanceof RestClient);
```

## Contextual bindings

Use `bind()` when one abstraction should resolve differently depending on which concrete class is being built.

```php
interface ClientInterface
{
}

final readonly class RestClient implements ClientInterface
{
}

final readonly class GraphQLClient implements ClientInterface
{
}

final readonly class GitHub
{
    public function __construct(
        private ClientInterface $client,
    ) {}

    public function client(): ClientInterface
    {
        return $this->client;
    }
}

$container->alias(ClientInterface::class, RestClient::class);
$container->bind(GitHub::class, ClientInterface::class, GraphQLClient::class);

$gitHub = $container->get(GitHub::class);

assert($gitHub->client() instanceof GraphQLClient);
assert($container->get(ClientInterface::class) instanceof RestClient);
```

## Factories

Register a factory when a service needs custom construction logic.

```php
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;

final readonly class Dependency
{
}

final readonly class Service
{
    public function __construct(
        private Dependency $dependency,
    ) {}

    public function dependency(): Dependency
    {
        return $this->dependency;
    }
}

/** @implements FactoryInterface<Service> */
final readonly class ServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new Service($container->get(Dependency::class));
    }
}

$container->factory(Service::class, ServiceFactory::class);

$service = $container->get(Service::class);

assert($service instanceof Service);
assert($service->dependency() instanceof Dependency);
```

## Extensions

Register an extension to mutate or decorate a service after it has been created.

```php
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;

final class GitHubClient
{
    public function __construct(
        private ?string $enterpriseUrl = null,
    ) {}

    public function setEnterpriseUrl(string $enterpriseUrl): void
    {
        $this->enterpriseUrl = $enterpriseUrl;
    }

    public function enterpriseUrl(): ?string
    {
        return $this->enterpriseUrl;
    }
}

/** @implements ExtensionInterface<GitHubClient> */
final readonly class GitHubClientExtension implements ExtensionInterface
{
    public function __invoke(ContainerInterface $container, object $service): void
    {
        $service->setEnterpriseUrl('https://github.example.com');
    }
}

$container->extend(GitHubClient::class, GitHubClientExtension::class);

$client = $container->get(GitHubClient::class);

assert($client->enterpriseUrl() === 'https://github.example.com');
```

## Service providers

Providers group related aliases, factories, extensions, and prebuilt instances.

Provider classes must implement [`Ghostwriter\Container\Interface\Service\ProviderInterface`](src/Interface/Service/ProviderInterface.php).

```php
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Ghostwriter\Container\Interface\Service\ProviderInterface;

interface TaskInterface
{
}

interface TaskCollectionInterface
{
    public function add(TaskInterface $task): void;

    public function count(): int;
}

final readonly class MainTask implements TaskInterface
{
    public function __construct(
        private string $name,
    ) {}
}

final readonly class FirstTask implements TaskInterface
{
    public function __construct(
        private string $name,
    ) {}
}

final class TaskCollection implements TaskCollectionInterface
{
    /** @var list<TaskInterface> */
    private array $tasks = [];

    public function add(TaskInterface $task): void
    {
        $this->tasks[] = $task;
    }

    public function count(): int
    {
        return count($this->tasks);
    }
}

/** @implements FactoryInterface<TaskCollection> */
final readonly class TaskCollectionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new TaskCollection();
    }
}

/** @implements ExtensionInterface<TaskCollection> */
final readonly class TaskCollectionExtension implements ExtensionInterface
{
    public function __invoke(ContainerInterface $container, object $service): void
    {
        $service->add(new FirstTask('Task 1'));
        $service->add($container->get(TaskInterface::class));
    }
}

final readonly class TasksProvider implements ProviderInterface
{
    public function boot(ContainerInterface $container): void
    {
        // no-op
    }

    public function register(BuilderInterface $builder): void
    {
        $builder->alias(TaskInterface::class, MainTask::class);
        $builder->alias(TaskCollectionInterface::class, TaskCollection::class);
        $builder->factory(TaskCollection::class, TaskCollectionFactory::class);
        $builder->extend(TaskCollection::class, TaskCollectionExtension::class);
        $builder->set(MainTask::class, new MainTask('Main Task'));
    }
}

$provider = $container->get(TasksProvider::class);
$provider->register($container);
$provider->boot($container);

$tasks = $container->get(TaskCollectionInterface::class);

assert($tasks instanceof TaskCollection);
assert($tasks->count() === 2);
```

## Automatic provider registration from Composer

The container can discover provider classes from Composer package metadata. Add the provider class under `extra.ghostwriter.container.provider` in your package's `composer.json`.

```json
{
    "extra": {
        "ghostwriter": {
            "container": {
                "provider": [
                    "Vendor\\Package\\Container\\PackageProvider",
                    "Vendor\\Package\\Container\\FeatureProvider"
                ]
            }
        }
    }
}
```

When the container is reset or first initialized, it reads Composer metadata, registers discovered providers, and boots them.

## PSR container interoperability

The package also provides a PSR-compatible wrapper class: [`Ghostwriter\Container\PsrContainer`](src/PsrContainer.php).

```php
use Ghostwriter\Container\Container;
use Ghostwriter\Container\PsrContainer;

$container = Container::getInstance();
$psrContainer = $container->get(\Psr\Container\ContainerInterface::class);
// or
//$psrContainer = new PsrContainer($container);

assert($psrContainer->has(Service::class) === true);
assert($psrContainer->get(Service::class) instanceof Service);
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` instead of using the issue tracker.

## Sponsors

[[`Become a GitHub Sponsor`](https://github.com/sponsors/ghostwriter)]

## Credits

- [Nathanael Esayeas](https://github.com/ghostwriter)
- [All Contributors](https://github.com/ghostwriter/container/contributors)

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.
