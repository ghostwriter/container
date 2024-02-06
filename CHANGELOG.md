# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/)
and this project adheres to [Semantic Versioning](https://semver.org/).

## [3.0.0] - 2024-02-06

### Added

- Method `invoke()` was added to interface `Ghostwriter\Container\Interface\ContainerInterface`

### Changed

- The number of required arguments for `Ghostwriter\Container\Instantiator#__construct()` increased from 0 to 2
- Parameter `$reflector` of `Ghostwriter\Container\Instantiator#construct()` was added
- Parameter `$parameterBuilder` of `Ghostwriter\Container\Instantiator#construct()` was added
- Parameter `$container` of `Ghostwriter\Container\ParameterBuilder#construct()` was added

### Fixed

- `ParameterBuilder` resolves positional arguments

### Removed

- Method `Ghostwriter\Container\Instantiator#buildParameters()` was removed
- Parameter `$container` of `Ghostwriter\Container\Instantiator#instantiate()` was removed
- Parameter `$container` of `Ghostwriter\Container\ParameterBuilder#build()` was removed

## [2.0.1] - 2023-11-15

### Added
- Add `factory` to register a [`service factory`](https://github.com/ghostwriter/container/blob/main/README.md#service-factory)

### Fixed
- `Build` resolves aliases
- `ParameterBuilder` resolves default/nullable values from the container
- `Extend` supports service name and service type

## [2.0.0] - 2023-11-12

### Changed
- Rename `bind` method name to `register`
- Rename `register` method name to `provide`
- Rename `provide` method name to `bind`
- Change `call` method parameter type to `callable`
- Change `extend` method 2nd parameter type to `string` (MUST be a `class-string` that implements `ExtensionInterface`)
- Change `invoke` method parameter type to `string` (MUST be `callable-string`, class with `__invoke` method or string function names `trim`)
- Change `set` method 2nd parameter type to `callable|object`. (The `callable` MUST return an `object`)

[2.0.1]: https://github.com/ghostwriter/container/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/ghostwriter/container/releases/tag/v2.0.0
