# Container

[![GitHub Sponsors](https://img.shields.io/github/sponsors/ghostwriter?label=Sponsor+@ghostwriter/container&logo=GitHub+Sponsors)](https://github.com/sponsors/ghostwriter)
[![Automation](https://github.com/ghostwriter/container/actions/workflows/automation.yml/badge.svg)](https://github.com/ghostwriter/container/actions/workflows/automation.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/container?color=8892bf)](https://www.php.net/supported-versions)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/container?color=blue)](https://packagist.org/packages/ghostwriter/container)

Provides an extensible Dependency Injection Service Container for Automated Object Composition, Interception, and Lifetime Management.

## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/container
```

## Usage

### Simple usage

Registering a service on the given container.

```php
final readonly class Service
{
    public function __construct(
        private Dependency $dependency
    ) {}

    public function dependency():Dependency
    {
        return $this->dependency;
    }
}

$container = Container::getInstance();

$service = $container->get(Service::class);

assert($service instanceof Service); // true

assert($service->dependency() instanceof Dependency); // true
```

### Automatic Service Definition Registration

> [!IMPORTANT]  
> A service definition class MUST implement `Ghostwriter\Container\Interface\Service\DefinitionInterface` [[class]](src/Interface/Service/DefinitionInterface.php).

Automatically register a service definition class using Composer's `extra` config in your `composer.json` file.

It should look like the following:

```json
{
    "extra": {
        "ghostwriter": {
            "container": {
                "definition": "App\\Service\\Definition"
            }
        }
    }
}
```

### Service Definition

Registering a service definition on the container.

```php
interface TaskInterface {}
interface TaskCollectionInterface {
    public function add(TaskInterface $task): void;
    public function count(): int;
}

final readonly class MainTask implements TaskInterface {
    public function __construct(
        private string $name
    ) {}
}

final readonly class FirstTask implements TaskInterface {
    public function __construct(
        private string $name
    ) {}
}

final class TaskCollection implements TaskCollectionInterface
{
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
final class TaskCollectionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): TaskCollection
    {
        return new TaskCollection();
    }
}
final class TaskCollectionExtension implements ExtensionInterface
{
    /** @param TaskCollection $service */
    public function __invoke(ContainerInterface $container, object $service): void
    {
        $service->add(new FirstTask('Task 1'));
        
        $mainTask = $container->build(TaskInterface::class, ['name' => 'Main Task']);
        
        assert($mainTask instanceof MainTask); // true
        
        $service->add($mainTask);
    }
}

final readonly class TasksServiceDefinition implements DefinitionInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $container->alias(MainTask::class, TaskInterface::class);
        $container->alias(TaskCollection::class, TaskCollectionInterface::class);
        $container->extend(TaskCollection::class, TaskCollectionExtension::class);
        $container->factory(TaskCollection::class, TaskCollectionFactory::class);
    }
}

$container = Container::getInstance();

$container->define(TasksDefinition::class);

$service = $container->get(TaskCollectionInterface::class); 
assert($service instanceof TaskCollection); // true

assert($service->count() === 2); // true
```

### Contextual Bindings

Registering a Contextual Bindings on the container.

```php
interface ClientInterface {}

final readonly class RestClient implements ClientInterface {}

final readonly class GraphQLClient implements ClientInterface {}

final readonly class GitHub
{
    public function __construct(
        private ClientInterface $client
    ) {
    }
    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}

// When GitHub::class asks for ClientInterface::class, it would receive an instance of GraphQLClient::class.
$container->bind(GitHub::class, ClientInterface::class, GraphQLClient::class);

// When any other service asks for ClientInterface::class, it would receive an instance of RestClient::class.
$container->alias(ClientInterface::class, RestClient::class);
```

### Service Extensions

Registering a service extension on the container.

```php
/**
 * @implements ExtensionInterface<GitHubClient>
 */
final readonly class GitHubExtension implements ExtensionInterface
{
    /**
     * @param GitHubClient $service
     */
    public function __invoke(ContainerInterface $container, object $service): void
    {
        $service->setEnterpriseUrl(
            $container->get(GitHubClient::GITHUB_HOST)
        );
    }
}

$container->alias(GitHubClientInterface::class, GitHubClient::class);
$container->extend(GitHubClientInterface::class, GitHubExtention::class);
```

### Service Factory

Registering a service factory on the container.

```php
final readonly class Dependency {}
final readonly class Service
{
    public function __construct(
        private Dependency $dependency
    ){}

    public function dependency():Dependency
    {
        return $this->dependency;
    }
}
final readonly class ServiceFactory {
  public function __invoke(Container $container): Service
  {
     return new Service($container->get(Dependency::class));
  }
}

$container = Container::getInstance();

$container->factory(Service::class, ServiceFactory::class);

$service = $container->get(Service::class);

assert($service instanceof Service); // true
assert($service->dependency() instanceof Dependency); // true
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

The BSD-4-Clause. Please see [License File](./LICENSE) for more information.
