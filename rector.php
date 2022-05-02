<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\PHPUnit\Set\PHPUnitLevelSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    // $rectorConfig->importNames();
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        PHPUnitLevelSetList::UP_TO_PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_EXCEPTION,
        PHPUnitSetList::REMOVE_MOCKS,
        PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD,
        PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        SetList::NAMING,
        SetList::PRIVATIZATION,
        SetList::PSR_4,
        SetList::RECTOR_CONFIG,
        SetList::TYPE_DECLARATION_STRICT,
        SetList::TYPE_DECLARATION,
    ]);
    $rectorConfig->paths([__DIR__ . '/rector.php', __DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->phpVersion(PhpVersion::PHP_80);

    // prefer self:: over $this for phpunit
    $rectorConfig->ruleWithConfiguration(
        PreferThisOrSelfMethodCallRector::class,
        [
            TestCase::class => PreferenceSelfThis::PREFER_SELF(),
        ]
    );

    // register single rule
    $rectorConfig->rule(TypedPropertyRector::class);
    $rectorConfig->ruleWithConfiguration(
        RenameMethodRector::class,
        [
            new MethodCallRename(TestCase::class, 'setExpectedException', 'expectedException'),
            new MethodCallRename(TestCase::class, 'setExpectedExceptionRegExp', 'expectedException'),
        ]
    );

    // $rectorConfig->ruleWithConfiguration(RenameNamespaceRector::class, [
    //     'Old\Name' => 'New\name',
    // ]);

    $rectorConfig->skip([
        __DIR__ . '*/tests/Fixture/*',
        __DIR__ . '*/vendor/*',
        // CallableThisArrayToAnonymousFunctionRector::class,
        RepeatedLiteralToClassConstantRector::class,
        PseudoNamespaceToNamespaceRector::class,
        StringClassNameToClassConstantRector::class,
        AddDoesNotPerformAssertionToNonAssertingTestRector::class,
    ]);
};
