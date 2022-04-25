# Container

[![Continuous Integration](https://github.com/ghostwriter/container/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ghostwriter/container/actions/workflows/continuous-integration.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/container?color=8892bf)](https://www.php.net/supported-versions)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/container/coverage.svg)](https://shepherd.dev/github/ghostwriter/container)
[![Infection MSI](https://img.shields.io/endpoint?url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fghostwriter%2Fcontainer%2Fmain)](https://github.com/ghostwriter/container)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/container)](https://packagist.org/packages/ghostwriter/container)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/container?color=blue)](https://packagist.org/packages/ghostwriter/container)

Provides an extensible [PSR-11](https://github.com/php-fig/fig-standards/blob/44a91bcff68f7b6a1479c459cc7c83dd32c7211e/accepted/PSR-11-container.md),
dependency injection service container with parameters and service
tagging.

## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/container
```

## Usage

``` php
use Ghostwriter\Container\Container;

class MyService
{
    private Dependency $dependency;

    public function __construct(Dependency $dependency)
    {
        $this->dependency = $dependency;
    }
}

$container = Container::getInstance();
$instance = $container->get(MyService::class); // MyService
```

### Simple usage

```php
$container = Container::getInstance();

$container->set('foobar', function (Container $container) {
    return new \stdClass();
},['tag']);

$container->get('foobar'); // stdClass
```

### Service Providers

```php
interface ServiceProviderInterface
{
    /**
     * Registers a service on the given container.
     */
    public function __invoke(ContainerInterface $container): void;
}

class MyServiceProvider implements ServiceProviderInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $container->set('my_service', function (Container $container) {
            $service = $container->build(MyService::class);

            foreach ($container->tagged('tag') as $serviceId => $params) {
                $service->add($container->get($serviceId));
            }

            return $service;
        });

        $container->set('tagged_service', function (Container $container) {
            return $container->build(MyService::class);
        }, [ 'tag' => [ 'param1' => 'foobar' ]);
    }
}
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
- [All Contributors](../../contributors)

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.
