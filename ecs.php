<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedInterfacesFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfStaticAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\ElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\SimplifiedIfReturnFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\FunctionNotation\StaticLambdaFixer;
use PhpCsFixer\Fixer\FunctionNotation\UseArrowFunctionsFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\GroupImportFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\LanguageConstruct\GetClassToClassKeywordFixer;
use PhpCsFixer\Fixer\Naming\NoHomoglyphNamesFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitConstructFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitInternalClassFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMockFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMockShortWillReturnFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitNamespacedFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitNoExpectationAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitSetUpTearDownVisibilityFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitSizeClassFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestClassRequiresCoversFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/rector.php',
        __DIR__ . '/ecs.php',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $parameters->set(Option::CACHE_DIRECTORY, '.cache/.ecs');
    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::SKIP, value: [
        '*/vendor/*',
        BinaryOperatorSpacesFixer::class,
        PhpdocLineSpanFixer::class,
        \PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer::class,
        PhpdocTrimFixer::class,
        GroupImportFixer::class
    ]);
    // A. full sets
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::NAMESPACES);
    $containerConfigurator->import(SetList::CONTROL_STRUCTURES);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::PHPUNIT);
    $containerConfigurator->import(SetList::STRICT);
    $containerConfigurator->import(SetList::SPACES);
    $containerConfigurator->import(SetList::ARRAY);
//    $containerConfigurator->import(SetList::PHP_CS_FIXER);
//    $containerConfigurator->import(SetList::SYMPLIFY);
    $services->set(SingleImportPerStatementFixer::class);
    $services->set(FullyQualifiedStrictTypesFixer::class);
    $services->set(GlobalNamespaceImportFixer::class);
    $services->set(NoUnusedImportsFixer::class);
    $services->set(DeclareStrictTypesFixer::class);
    $services->set(StrictComparisonFixer::class);
    $services->set(SemicolonAfterInstructionFixer::class);
    $services->set(NoEmptyStatementFixer::class);
    $services->set(StrictParamFixer::class);
    $services->set(ReturnTypeDeclarationFixer::class);
    $services->set(NoHomoglyphNamesFixer::class);
    $services->set(FinalClassFixer::class);
    $services->set(ProtectedToPrivateFixer::class);

//    $services->set(ClassReferenceNameCasingFixer::class);
//    $services->set(IntegerLiteralCaseFixer::class);
//    $services->set(LowercaseKeywordsFixer::class);
//    $services->set(LowercaseStaticReferenceFixer::class);
//    $services->set(MagicConstantCasingFixer::class);
//    $services->set(MagicMethodCasingFixer::class);
//    $services->set(NativeFunctionCasingFixer::class);
//    $services->set(NativeFunctionTypeDeclarationCasingFixer::class);

    $services->set(YodaStyleFixer::class);
    $services->set(SelfAccessorFixer::class);
    $services->set(SelfStaticAccessorFixer::class);
    $services->set(SingleClassElementPerStatementFixer::class);
    $services->set(VisibilityRequiredFixer::class);

    $services->set(ElseifFixer::class);
    $services->set(SimplifiedIfReturnFixer::class);
    $services->set(NoSuperfluousElseifFixer::class);
    $services->set(StaticLambdaFixer::class);
    $services->set(UseArrowFunctionsFixer::class);
    $services->set(NoUnusedImportsFixer::class);
    $services->set(GetClassToClassKeywordFixer::class);

    $services->set(ConstantCaseFixer::class)->call('configure', [['case' => 'lower']]);;
    $services->set(ArraySyntaxFixer::class)->call('configure', [['syntax' => 'short']]);
    $services->set(OrderedClassElementsFixer::class)->call('configure', [['sort_algorithm' => 'alpha']]);
    $services->set(OrderedInterfacesFixer::class)->call('configure', [['order' => 'alpha']]);
    $services->set(OrderedImportsFixer::class)->call('configure', [['imports_order' => ['class', 'const', 'function']]]);

    $services->set(PhpUnitConstructFixer::class);
    $services->set(PhpUnitDedicateAssertFixer::class);
    $services->set(PhpUnitDedicateAssertInternalTypeFixer::class);
    $services->set(PhpUnitExpectationFixer::class);
    $services->set(PhpUnitFqcnAnnotationFixer::class);
    $services->set(PhpUnitInternalClassFixer::class);
    $services->set(PhpUnitMethodCasingFixer::class);
    $services->set(PhpUnitMockFixer::class);
    $services->set(PhpUnitMockShortWillReturnFixer::class);
    $services->set(PhpUnitNamespacedFixer::class);
    $services->set(PhpUnitNoExpectationAnnotationFixer::class);
    $services->set(PhpUnitSetUpTearDownVisibilityFixer::class);
    $services->set(PhpUnitSizeClassFixer::class);
    $services->set(PhpUnitStrictFixer::class);
    $services->set(PhpUnitTestAnnotationFixer::class);
    $services->set(PhpUnitTestCaseStaticMethodCallsFixer::class)->call('configure', [['call_type' => 'self']]);
    $services->set(PhpUnitTestClassRequiresCoversFixer::class);
};
