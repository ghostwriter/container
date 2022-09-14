<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\RequireStrictTypesSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\VersionControl\GitMergeConflictSniff;
use PhpCsFixer\Fixer\Alias\MbStrFunctionsFixer;
use PhpCsFixer\Fixer\Alias\ModernizeStrposFixer;
use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer;
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
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\LanguageConstruct\GetClassToClassKeywordFixer;
use PhpCsFixer\Fixer\Naming\NoHomoglyphNamesFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer;
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
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use SlevomatCodingStandard\Sniffs\Complexity\CognitiveSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\AssignmentInConditionSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\BlockControlStructureSpacingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\JumpStatementsSpacingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\LanguageConstructWithParenthesesSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\NewWithParenthesesSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireMultiLineConditionSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireMultiLineTernaryOperatorSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireNullCoalesceEqualOperatorSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireNullCoalesceOperatorSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireNullSafeObjectOperatorSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireShortTernaryOperatorSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireSingleLineConditionSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireTernaryOperatorSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireYodaComparisonSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessIfConditionWithReturnSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessTernaryOperatorSniff;
use SlevomatCodingStandard\Sniffs\Operators\SpreadOperatorSpacingSniff;
use SlevomatCodingStandard\Sniffs\PHP\ReferenceSpacingSniff;
use SlevomatCodingStandard\Sniffs\PHP\RequireNowdocSniff;
use SlevomatCodingStandard\Sniffs\PHP\TypeCastSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessSemicolonSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->cacheDirectory(__DIR__ . '/.cache/ecs');
    $ecsConfig->parallel();
    $ecsConfig->paths([
        __DIR__ . '/ecs.php',
        __DIR__ . '/README.md',
        __DIR__ . '/rector.php',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->rules([
        DeclareStrictTypesFixer::class,
        ElseifFixer::class,
        FinalClassFixer::class,
        FullyQualifiedStrictTypesFixer::class,
        GetClassToClassKeywordFixer::class,
        GitMergeConflictSniff::class,
        LowercaseKeywordsFixer::class,
        LowercaseStaticReferenceFixer::class,
        MagicConstantCasingFixer::class,
        MagicMethodCasingFixer::class,
        MbStrFunctionsFixer::class,
        ModernizeStrposFixer::class,
        NoAliasFunctionsFixer::class,
        NoEmptyStatementFixer::class,
        NoHomoglyphNamesFixer::class,
        NoLeadingImportSlashFixer::class,
        NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
        NoSuperfluousElseifFixer::class,
        NoUnusedImportsFixer::class,
        PhpUnitConstructFixer::class,
        PhpUnitDedicateAssertFixer::class,
        PhpUnitDedicateAssertInternalTypeFixer::class,
        PhpUnitExpectationFixer::class,
        PhpUnitFqcnAnnotationFixer::class,
        PhpUnitInternalClassFixer::class,
        PhpUnitMethodCasingFixer::class,
        PhpUnitMockFixer::class,
        PhpUnitMockShortWillReturnFixer::class,
        PhpUnitNamespacedFixer::class,
        PhpUnitNoExpectationAnnotationFixer::class,
        PhpUnitSetUpTearDownVisibilityFixer::class,
        PhpUnitSizeClassFixer::class,
        PhpUnitStrictFixer::class,
        PhpUnitTestAnnotationFixer::class,
        PhpUnitTestClassRequiresCoversFixer::class,
        PhpdocAnnotationWithoutDotFixer::class,
        PhpdocOrderFixer::class,
        PhpdocSeparationFixer::class,
        PhpdocSummaryFixer::class,
        PhpdocTypesOrderFixer::class,
        ProtectedToPrivateFixer::class,
        RandomApiMigrationFixer::class,
        ReturnTypeDeclarationFixer::class,
        SelfAccessorFixer::class,
        SelfStaticAccessorFixer::class,
        SemicolonAfterInstructionFixer::class,
        SimplifiedIfReturnFixer::class,
        SingleClassElementPerStatementFixer::class,
        SingleImportPerStatementFixer::class,
        StaticLambdaFixer::class,
        StrictComparisonFixer::class,
        StrictParamFixer::class,
        UseArrowFunctionsFixer::class,
        VisibilityRequiredFixer::class,
        // YodaStyleFixer::class,
    ]);

    $ecsConfig->rule(LineLengthFixer::class);
    // $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff::class);
    // $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff::class);
    $ecsConfig->rule(ReferenceSpacingSniff::class);
    $ecsConfig->rule(TypeCastSniff::class);
    $ecsConfig->rule(RequireNowdocSniff::class);
    $ecsConfig->rule(SpreadOperatorSpacingSniff::class);
    $ecsConfig->rule(UselessParenthesesSniff::class);
    $ecsConfig->rule(UselessSemicolonSniff::class);

    $ecsConfig->rule(AssignmentInConditionSniff::class);
    $ecsConfig->rule(BlockControlStructureSpacingSniff::class);
    // $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\ControlStructures\DisallowContinueWithoutIntegerOperandInSwitchSniff::class);
    $ecsConfig->rule(DisallowEmptySniff::class);
    // $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\ControlStructures\DisallowNullSafeObjectOperatorSniff::class);
    // $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\ControlStructures\DisallowShortTernaryOperatorSniff::class);
    // $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff::class);
    $ecsConfig->rule(EarlyExitSniff::class);
    $ecsConfig->rule(JumpStatementsSpacingSniff::class);
    $ecsConfig->rule(LanguageConstructWithParenthesesSniff::class);
    // $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\ControlStructures\NewWithoutParenthesesSniff::class);
    $ecsConfig->rule(NewWithParenthesesSniff::class);
    $ecsConfig->rule(RequireMultiLineConditionSniff::class);
    $ecsConfig->rule(RequireMultiLineTernaryOperatorSniff::class);
    $ecsConfig->rule(RequireNullCoalesceEqualOperatorSniff::class);
    $ecsConfig->rule(RequireNullCoalesceOperatorSniff::class);
    $ecsConfig->rule(RequireNullSafeObjectOperatorSniff::class);
    $ecsConfig->rule(RequireShortTernaryOperatorSniff::class);
    $ecsConfig->rule(RequireSingleLineConditionSniff::class);
    $ecsConfig->rule(RequireTernaryOperatorSniff::class);
    $ecsConfig->rule(RequireYodaComparisonSniff::class);
    $ecsConfig->rule(UselessIfConditionWithReturnSniff::class);
    $ecsConfig->rule(UselessTernaryOperatorSniff::class);
    // $ecsConfig->rule(CognitiveSniff::class);
    // $ecsConfig->rule(DeclareStrictTypesSniff::class);
    // $ecsConfig->rule(RequireStrictTypesSniff::class);
    // $ecsConfig->rule(RedundantSemicolon::class);

    $ecsConfig->ruleWithConfiguration(GlobalNamespaceImportFixer::class, [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ]);
    $ecsConfig->ruleWithConfiguration(GlobalNamespaceImportFixer::class, [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ]);
    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, [
        'imports_order' => ['class', 'const', 'function'],
    ]);
    $ecsConfig->ruleWithConfiguration(OrderedClassElementsFixer::class, [
        'sort_algorithm' => 'alpha',
    ]);
    $ecsConfig->ruleWithConfiguration(OrderedInterfacesFixer::class, [
        'order' => 'alpha',
    ]);
    $ecsConfig->ruleWithConfiguration(PhpdocAlignFixer::class, [
        'tags' => ['method', 'param', 'property', 'return', 'throws', 'type', 'var'],
    ]);
    $ecsConfig->ruleWithConfiguration(PhpUnitTestCaseStaticMethodCallsFixer::class, [
        'call_type' => 'self',
    ]);
    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);
    $ecsConfig->ruleWithConfiguration(ConstantCaseFixer::class, [
        'case' => 'lower',
    ]);

    $ecsConfig->sets([
        SetList::ARRAY,
        SetList::CLEAN_CODE,
        SetList::COMMON,
        SetList::CONTROL_STRUCTURES,
        SetList::NAMESPACES,
        SetList::PSR_12,
        SetList::DOCBLOCK,
        SetList::PHPUNIT,
        SetList::SPACES,
        SetList::STRICT,
        SetList::SYMPLIFY,
    ]);

    $ecsConfig->skip([
        '*/tests/Fixture/*',
        '*/vendor/*',
        GroupImportFixer::class,
        BinaryOperatorSpacesFixer::class,
        GeneralPhpdocAnnotationRemoveFixer::class,
        PhpdocLineSpanFixer::class,
        PhpdocTrimFixer::class,
        YodaStyleFixer::class,
    ]);
};
