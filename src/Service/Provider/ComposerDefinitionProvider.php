<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Service\Provider;

use Ghostwriter\Container\Exception\ShouldNotHappenException;
use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\Provider\ComposerDefinitionProviderInterface;
use Ghostwriter\Container\Interface\Service\ProviderInterface;

use InvalidArgumentException;
use Override;
use Throwable;

use const DIRECTORY_SEPARATOR;
use const JSON_THROW_ON_ERROR;

use function array_key_exists;
use function dirname;

use function file_exists;
use function file_get_contents;
use function get_debug_type;
use function implode;
use function is_a;
use function is_array;
use function is_dir;
use function is_string;
use function json_decode;
use function mb_rtrim;
use function mb_trim;
use function sprintf;

final class ComposerDefinitionProvider implements ComposerDefinitionProviderInterface
{
    private const array COMPOSER_JSON_SEGMENTS = ['composer.json'];

    private const array EXTRA_GHOSTWRITER_CONTAINER_PROVIDER = ['extra', 'ghostwriter', 'container', 'provider'];

    private const array INSTALLED_JSON_SEGMENTS = ['vendor', 'composer', 'installed.json'];

    private const array VENDOR_AUTOLOAD_SEGMENTS = ['vendor', 'autoload.php'];

    private bool $booted = false;

    /** @var array<class-string<ProviderInterface>,bool> */
    private array $providers = [];

    private bool $registered = false;

    /** @throws Throwable */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
        $rootPath = $this->projectRootPath();
        foreach ($this->installedPackages($rootPath) as $package) {
            $this->addProviderFromPackage($package);
        }
    }

    /** @param class-string<ProviderInterface> $provider */
    #[Override]
    public function add(string $provider): void
    {
        if ($this->booted) {
            throw new InvalidArgumentException('Container providers have already been booted.');
        }

        if ($this->has($provider)) {
            return;
        }

        $this->providers[$provider] = true;
    }

    /** @throws Throwable */
    #[Override]
    public function boot(ContainerInterface $container): void
    {
        if ($this->booted) {
            throw new InvalidArgumentException('Container providers have already been booted.');
        }

        $this->booted = true;

        foreach ($this->providers as $provider => $_) {
            $this->container->get($provider)->boot($container);
        }
    }

    /** @throws Throwable */
    #[Override]
    public function register(BuilderInterface $builder): void
    {
        if ($this->registered) {
            throw new InvalidArgumentException('Container providers have already been registered.');
        }

        $this->registered = true;

        foreach ($this->providers as $provider => $_) {
            $this->container->get($provider)->register($builder);
        }
    }

    /**
     * @param array<array<string, mixed>, mixed> $package
     *
     * @throws Throwable
     */
    private function addProviderFromPackage(array $package): void
    {
        $providers = $this->packageProviderValue($package);

        if (null === $providers) {
            return;
        }

        if (is_string($providers)) {
            $providers = [$providers];
        }

        if (! is_array($providers)) {
            throw new ShouldNotHappenException(
                sprintf(
                    'Composer extra configuration "%s" for package %s MUST be a class-string<%s> or an array of class-string<%s>, %s given',
                    implode('.', self::EXTRA_GHOSTWRITER_CONTAINER_PROVIDER),
                    $this->packageName($package),
                    ProviderInterface::class,
                    ProviderInterface::class,
                    get_debug_type($providers)
                )
            );
        }

        foreach ($providers as $provider) {
            $this->add($provider);
        }
    }

    /**
     * @throws Throwable
     *
     * @return array<string, mixed>
     */
    private function composerJson(string $composerJsonPath): array
    {
        return $this->decodeJsonFile($composerJsonPath, 'composer.json');
    }

    /**
     * @throws Throwable
     *
     * @return array<string, mixed>
     */
    private function decodeJsonFile(string $path, string $label): array
    {
        $json = json_decode($this->readFileContents($path, $label), true, 512, JSON_THROW_ON_ERROR);

        if (is_array($json)) {
            return $json;
        }

        throw new ShouldNotHappenException(sprintf('Could not decode %s at %s', $label, $path));
    }

    /**
     * @param string $rootPath
     *
     * @return string
     */
    private function getComposerJsonPath(string $rootPath): string
    {
        return $this->path($rootPath, self::COMPOSER_JSON_SEGMENTS);
    }

    /**
     * @param string $rootPath
     *
     * @return string
     */
    private function getInstalledJsonPath(string $rootPath): string
    {
        return $this->path($rootPath, self::INSTALLED_JSON_SEGMENTS);
    }

    /** @param class-string<ProviderInterface> $provider */
    private function has(string $provider): bool
    {
        if (! is_a($provider, ProviderInterface::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Provider "%s" must implement "%s".',
                $provider,
                ProviderInterface::class,
            ));
        }

        if (self::class === $provider) {
            return true;
        }

        return array_key_exists($provider, $this->providers);
    }

    /**
     * @throws Throwable
     *
     * @return list<array{name: string, extra?: array{ghostwriter?: array{container?: array{provider?: string}}}}>
     */
    private function installedJson(string $composerJsonPath): array
    {
        $installedJson = $this->decodeJsonFile($this->installedJsonPath($composerJsonPath), 'installed.json');

        if (! array_key_exists('packages', $installedJson)) {
            /** @var list<array{name: string, extra?: array{ghostwriter?: array{container?: array{provider?: string}}}}>} */
            return $installedJson;
        }

        /** @var list<array{name: string, extra?: array{ghostwriter?: array{container?: array{provider?: string}}}}>} */
        return $installedJson['packages'];
    }

    private function installedJsonPath(string $composerJsonPath): string
    {
        $installedJsonPath = $this->getInstalledJsonPath(dirname($composerJsonPath));

        if (file_exists($installedJsonPath)) {
            return $installedJsonPath;
        }

        throw new ShouldNotHappenException(sprintf('Could not find installed.json at %s', $installedJsonPath));
    }

    /**
     * @throws Throwable
     *
     * @return list<array{name: string, extra?: array{ghostwriter?: array{container?: array{provider?: string}}}}>
     */
    private function installedPackages(string ...$rootPaths): array
    {
        $packages = [];

        foreach ($rootPaths as $rootPath) {
            if (! $this->rootComposerFilesExist($rootPath)) {
                continue;
            }

            $composerJsonPath = $this->getComposerJsonPath($rootPath);

            $packages = [
                ...$packages,
                ...$this->installedJson($composerJsonPath),
                $this->composerJson($composerJsonPath),
            ];
        }

        return $packages;
    }

    /**
     * @param array<string, mixed> $package
     *
     * @return non-empty-string
     */
    private function packageName(array $package): string
    {
        $name = $package['name'] ?? throw new ShouldNotHappenException(
            'Package name not found in composer.json or installed.json'
        );

        if (! is_string($name) || '' === mb_trim($name)) {
            throw new ShouldNotHappenException('Package name not found in composer.json or installed.json');
        }

        return $name;
    }

    /**
     * @param array<string, mixed> $package
     *
     * @return null|class-string<ProviderInterface>
     */
    private function packageProviderValue(array $package): mixed
    {
        $value = $package;

        foreach (self::EXTRA_GHOSTWRITER_CONTAINER_PROVIDER as $key) {
            if (! is_array($value) || ! array_key_exists($key, $value)) {
                return null;
            }

            $value = $value[$key];
        }

        return $value;
    }

    /** @param list<non-empty-string> $segments */
    private function path(string $basePath, array $segments): string
    {
        return implode(DIRECTORY_SEPARATOR, [mb_rtrim($basePath, DIRECTORY_SEPARATOR), ...$segments]);
    }

    /** @return non-empty-string */
    private function projectRootPath(): string
    {
        static $cachedRootPath = null;
        if (is_string($cachedRootPath)) {
            return $cachedRootPath;
        }

        $currentDirectory = __DIR__ . DIRECTORY_SEPARATOR;

        while (! file_exists($this->path($currentDirectory, self::VENDOR_AUTOLOAD_SEGMENTS))) {
            $parentDirectory = dirname($currentDirectory);

            if ($parentDirectory === $currentDirectory) {
                throw new ShouldNotHappenException(sprintf(
                    'Could not find %s in any parent directory.',
                    implode(DIRECTORY_SEPARATOR, self::VENDOR_AUTOLOAD_SEGMENTS)
                ));
            }

            $currentDirectory = $parentDirectory . DIRECTORY_SEPARATOR;
        }

        return $cachedRootPath = $currentDirectory;
    }

    /** @throws Throwable */
    private function readFileContents(string $path, string $label): string
    {
        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new ShouldNotHappenException(sprintf('Could not read %s at %s', $label, $path));
        }

        return $contents;
    }

    private function rootComposerFilesExist(string $rootPath): bool
    {
        if (! is_dir($rootPath)) {
            return false;
        }

        return file_exists($this->getComposerJsonPath($rootPath)) && file_exists(
            $this->getInstalledJsonPath($rootPath)
        );
    }
}
