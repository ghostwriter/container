# Container

[![Continuous Integration](https://github.com/ghostwriter/container/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ghostwriter/container/actions/workflows/continuous-integration.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/container?color=8892bf)](https://www.php.net/supported-versions)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/container/coverage.svg)](https://shepherd.dev/github/ghostwriter/container)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/container)](https://packagist.org/packages/ghostwriter/container)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/container?color=blue)](https://packagist.org/packages/ghostwriter/container)

Provides an extensible [PSR-11](https://www.php-fig.org/psr/psr-11/), dependency injection service container.

## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/container
```

## Usage

### Simple usage

Registering a service on the given container.

```php
class Service
{
    private Dependency $dependency;
    public function __construct(Dependency $dependency)
    {
        $this->dependency = $dependency;
    }
    public function dependency():Dependency
    {
        return $this->dependency;
    }
}

use Ghostwriter\Container\Container;

$container = Container::getInstance();
$service = $container->get(Service::class);

assert($service instanceof Service); // true
assert($service->dependency() instanceof Dependency); // true
```

### Service Providers

Registering a service provider on the container.

```php
class TaskInterface{}

class Task extends TaskInterface{}

class Tasks
{
    private array $tasks;
    public function addTask(TaskInterface $task)
    {
        $this->tasks[$task::class] = $task;
    }
}

class TasksServiceProvider implements ServiceProviderInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $container->bind(Task::class);

        $container->alias(TaskInterface::class, Task::class);

        $container->set(Tasks::class, function (Container $container) {
            /** @var Tasks $tasks */
            $tasks = $container->build(Tasks::class);

            foreach ($container->tagged(Task::class) as $serviceId) {
                $tasks->addTask($container->get($serviceId));
            }

            return $tasks;
        }, [Tasks::class, 'tasks']);
    }
}

$container->register(new TasksServiceProvider());
```

### Service Extensions

Registering a service extension on the container.

```php
$container->extend(GitHubClient::class, function (Container $container, object $client) {
    $client->setEnterpriseUrl($client->get(GitHubClient::GITHUB_HOST));
});

// or

class GitHubExtension implements ExtensionInterface
{
    public function __invoke(ContainerInterface $container, object $service): object
    {
        $service->setEnterpriseUrl($container->get(GitHubClient::GITHUB_HOST));

        return $service;
    }
}

$container->add(GitHubClient::class, $container->get(GitHubExtention::class));
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` instead of using the issue tracker.

## Credits

- [Nathanael Esayeas](https://github.com/ghostwriter)
- [All Contributors](https://github.com/ghostwriter/container/contributors)

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.
