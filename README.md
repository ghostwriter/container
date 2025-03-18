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

### Attributes

Registering services using attributes.

#### `#[Inject]`

Registering a service on the container using attributes.

```php
use Ghostwriter\Container\Attribute\Inject;

final readonly class Service
{
    public function __invoke(
        #[Inject(Dependency::class)]
        DependencyInterface $dependency
    ): Dependency
    {
        return $this->dependency;
    }
}

// the above is equivalent to the following
// $container->alias(Dependency::class, DependencyInterface::class);

final readonly class Service
{
    public function __construct(
        #[Inject(Dependency::class)]
        private DependencyInterface $dependency
    ) {}

    public function dependency():Dependency
    {
        return $this->dependency;
    }
}

// the above is equivalent to the following
// $container->bind(Service::class, DependencyInterface::class, Dependency::class);
```

---

Registering a service factory on the container using attributes.

#### `#[Factory]`

```php
use Ghostwriter\Container\Attribute\Factory;

#[Factory(ServiceFactory::class)]
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

// $container->factory(Service::class, ServiceFactory::class);
```

---

#### `#[Extension]`

Registering a service extension on the container using attributes.

```php
use Ghostwriter\Container\Attribute\Extension;

#[Extension(ServiceExtension::class)]
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

// the above is equivalent to the following
// $container->extend(Service::class, ServiceExtension::class);
```

---

#### `#[Provider]`

Registering a service provider on the container using attributes.

```php
use Ghostwriter\Container\Attribute\Provider;

#[Provider(ServiceProvider::class)]
final readonly class Service
{
    public function __construct(
        private DependencyInterface $dependency
    ) {}

    public function dependency():DependencyInterface
    {
        return $this->dependency;
    }
}

// the above is equivalent to the following
// $container->provide(ServiceProvider::class);
```

### Service Providers

Registering a service provider on the container.

```php
interface TaskInterface {}

final readonly class Task implements TaskInterface {}

final class Tasks
{
    private array $tasks = [];
    public function addTask(TaskInterface $task)
    {
        $this->tasks[] = $task;
    }
}

final readonly class TasksServiceProvider implements ServiceProviderInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $container->alias(Task::class, TaskInterface::class);
        
        // "set" the service instance
        $container->set(FirstTask::class, new FirstTask(), [Task::class]);
        
        // "define" the service builder
        $container->define(Tasks::class, static function (Container $container) {
            /** @var Tasks $tasks */
            $tasks = $container->build(Tasks::class);

            foreach ($container->tagged(Task::class) as $service) {
                $tasks->addTask($service);
            }

            return $tasks;
        }, [Tasks::class, 'tasks']);
        
    }
}

$container->provide(TasksServiceProvider::class);

$service = $container->get(TaskInterface::class);

assert($service instanceof Task); // true
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
     * @return GitHubClient
     */
    public function __invoke(ContainerInterface $container, object $service): object
    {
        $service->setEnterpriseUrl(
            $container->get(GitHubClient::GITHUB_HOST)
        );

        return $service;
    }
}

$container->alias(GitHubClientInterface::class, GitHubClient::class);
$container->extend(GitHubClientInterface::class, $container->get(GitHubExtention::class));
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
