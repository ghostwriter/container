<?php

declare(strict_types=1);

namespace Ghostwriter\Container\Service\Definition;

use Generator;
use Ghostwriter\Container\Exception\ShouldNotHappenException;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Throwable;

use const DIRECTORY_SEPARATOR;
use const JSON_THROW_ON_ERROR;

use function array_key_exists;
use function array_reduce;
use function dirname;
use function file_exists;
use function file_get_contents;
use function get_debug_type;
use function implode;
use function is_a;
use function is_array;
use function is_string;
use function json_decode;
use function sprintf;

final class ComposerExtraDefinition implements DefinitionInterface
{
    private const array KEY = ['extra', 'ghostwriter', 'container', 'definition'];

    /** @throws Throwable */
    public function __invoke(ContainerInterface $container): void
    {
        foreach ($this->installedDefinitions() as $definition) {
            $container->define($definition);
        }
    }

    /**
     * @throws Throwable
     *
     * @return array<string, mixed>
     */
    private function composerJson(string $composerJsonPath): array
    {
        $composerJsonContents = file_get_contents($composerJsonPath);
        if (false === $composerJsonContents) {
            throw new ShouldNotHappenException(sprintf('Could not read composer.json at %s', $composerJsonPath));
        }

        $composerJson = json_decode($composerJsonContents, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($composerJson)) {
            throw new ShouldNotHappenException(sprintf('Could not decode composer.json at %s', $composerJsonPath));
        }

        return $composerJson;
    }

    private function composerJsonPath(): string
    {
        $filename =  implode(DIRECTORY_SEPARATOR, ['vendor', 'autoload.php']);

        $currentDirectory = __DIR__ . DIRECTORY_SEPARATOR;

        while (! file_exists($currentDirectory . $filename)) {
            $parentDirectory = dirname($currentDirectory);

            if ($parentDirectory === $currentDirectory) {
                throw new ShouldNotHappenException(sprintf(
                    'Could not find %s in any parent directory.',
                     $filename
                ));
            }

            $currentDirectory = $parentDirectory . DIRECTORY_SEPARATOR;
        }

        return $currentDirectory . 'composer.json';
    }

    /**
     * @throws Throwable
     *
     * @return Generator<class-string<DefinitionInterface>>
     */
    private function installedDefinitions(): iterable
    {
        foreach ($this->installedPackages() as $package) {
            $value = array_reduce(
                self::KEY,
                static fn ($carry, string $key): mixed => match (true) {
                    is_array($carry) => $carry[$key] ?? null,
                    default => null,
                },
                $package
            );

            if (null === $value) {
                continue;
            }

            $name = $package['name'] ?? throw new ShouldNotHappenException(
                'Package name not found in composer.json or installed.json'
            );

            if (! is_string($value) || ! is_a($value, DefinitionInterface::class, true)) {
                throw new ShouldNotHappenException(
                    sprintf(
                        'Composer extra configuration "%s" for package %s MUST be a class-string<%s>, %s given',
                        implode('.', self::KEY),
                        $name,
                        DefinitionInterface::class,
                        get_debug_type($value)
                    )
                );
            }

            yield $name => $value;
        }
    }

    /**
     *
     * @throws Throwable
     *
     * @return list<array{name: string, extra?: array{ghostwriter?: array{container?: array{provider?: string}}}}>
     */
    private function installedJson(string $composerJsonPath): array
    {
        $installedJsonContents = $this->installedJsonContents($composerJsonPath);

        $installedJson = json_decode($installedJsonContents, true, 512, JSON_THROW_ON_ERROR);

        if (! array_key_exists('packages', $installedJson)) {
            return $installedJson;
        }

        return $installedJson['packages'];
    }

    /** @throws Throwable */
    private function installedJsonContents(string $composerJsonPath): string
    {
        $installedJsonPath = $this->installedJsonPath($composerJsonPath);

        $installedJsonContents = file_get_contents($installedJsonPath);
        if (false === $installedJsonContents) {
            throw new ShouldNotHappenException(sprintf('Could not read installed.json at %s', $installedJsonPath));
        }

        return $installedJsonContents;
    }

    /**
     * @param $composerJsonPath
     *
     * @return string
     */
    private function installedJsonPath($composerJsonPath): string
    {
        $installedJsonPath = implode(
            DIRECTORY_SEPARATOR,
            [dirname($composerJsonPath), 'vendor', 'composer', 'installed.json']
        );

        if (! file_exists($installedJsonPath)) {
            throw new ShouldNotHappenException(sprintf('Could not find installed.json at %s', $installedJsonPath));
        }

        return $installedJsonPath;
    }

    /**
     * @throws Throwable
     *
     * @return list<array{name: string, extra?: array{ghostwriter?: array{container?: array{provider?: string}}}}>
     */
    private function installedPackages(): array
    {
        $composerJsonPath = $this->composerJsonPath();

        $packages = $this->installedJson($composerJsonPath);

        $packages[] = $this->composerJson($composerJsonPath);

        return $packages;
    }
}
