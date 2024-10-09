<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

use Contao\EasyCodingStandard\Fixer\AssertEqualsFixer;
use Contao\EasyCodingStandard\Fixer\CaseCommentIndentationFixer;
use Contao\EasyCodingStandard\Fixer\ChainedMethodBlockFixer;
use Contao\EasyCodingStandard\Fixer\CommentLengthFixer;
use Contao\EasyCodingStandard\Fixer\ExpectsWithCallbackFixer;
use Contao\EasyCodingStandard\Fixer\FindByPkFixer;
use Contao\EasyCodingStandard\Fixer\FunctionCallWithMultilineArrayFixer;
use Contao\EasyCodingStandard\Fixer\InlinePhpdocCommentFixer;
use Contao\EasyCodingStandard\Fixer\IsArrayNotEmptyFixer;
use Contao\EasyCodingStandard\Fixer\MockMethodChainingIndentationFixer;
use Contao\EasyCodingStandard\Fixer\MultiLineIfIndentationFixer;
use Contao\EasyCodingStandard\Fixer\MultiLineLambdaFunctionArgumentsFixer;
use Contao\EasyCodingStandard\Fixer\NoExpectsThisAnyFixer;
use Contao\EasyCodingStandard\Fixer\NoLineBreakBetweenMethodArgumentsFixer;
use Contao\EasyCodingStandard\Fixer\SingleLineConfigureCommandFixer;
use Contao\EasyCodingStandard\Fixer\TypeHintOrderFixer;
use Contao\EasyCodingStandard\Sniffs\ContaoFrameworkClassAliasSniff;
use Contao\EasyCodingStandard\Sniffs\SetDefinitionCommandSniff;
use Contao\EasyCodingStandard\Sniffs\UseSprintfInExceptionsSniff;
use PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfStaticAccessorFixer;
use PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer;
use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\RegularCallableCallFixer;
use PhpCsFixer\Fixer\FunctionNotation\StaticLambdaFixer;
use PhpCsFixer\Fixer\FunctionNotation\UseArrowFunctionsFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NoUnsetOnPropertyFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderByValueFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer;
use PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderReturnTypeFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderStaticFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMockFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitNamespacedFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitNoExpectationAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer;
use PhpCsFixer\Fixer\StringNotation\StringImplicitBackslashesFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\HeredocIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocTypesCommaSpacesFixer;
use PhpCsFixerCustomFixers\Fixer\PhpUnitAssertArgumentsOrderFixer;
use SlevomatCodingStandard\Sniffs\Functions\UnusedInheritedVariablePassedToClosureSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UnusedUsesSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UselessAliasSniff;
use SlevomatCodingStandard\Sniffs\Operators\RequireCombinedAssignmentOperatorSniff;
use SlevomatCodingStandard\Sniffs\PHP\DisallowDirectMagicInvokeCallSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\UselessConstantTypeHintSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff;
use SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([
        __DIR__.'/set/psr12.php',
        __DIR__.'/set/symfony.php',
    ])
    ->withRules([
        ArrayIndentationFixer::class,
        AssertEqualsFixer::class,
        BlankLineAfterStrictTypesFixer::class,
        CaseCommentIndentationFixer::class,
        ChainedMethodBlockFixer::class,
        ClassAttributesSeparationFixer::class,
        CombineConsecutiveIssetsFixer::class,
        CombineConsecutiveUnsetsFixer::class,
        CommentLengthFixer::class,
        ContaoFrameworkClassAliasSniff::class,
        DeclareStrictTypesFixer::class,
        DisallowArrayTypeHintSyntaxSniff::class,
        DisallowDirectMagicInvokeCallSniff::class,
        ExpectsWithCallbackFixer::class,
        FindByPkFixer::class,
        FunctionCallWithMultilineArrayFixer::class,
        HeredocIndentationFixer::class,
        HeredocToNowdocFixer::class,
        InlinePhpdocCommentFixer::class,
        IsArrayNotEmptyFixer::class,
        MethodChainingIndentationFixer::class,
        MockMethodChainingIndentationFixer::class,
        MultilineCommentOpeningClosingFixer::class,
        MultiLineIfIndentationFixer::class,
        MultiLineLambdaFunctionArgumentsFixer::class,
        NoExpectsThisAnyFixer::class,
        NoLineBreakBetweenMethodArgumentsFixer::class,
        NoSuperfluousElseifFixer::class,
        NoUnsetOnPropertyFixer::class,
        NoUselessElseFixer::class,
        NoUselessReturnFixer::class,
        NullTypeHintOnLastPositionSniff::class,
        OrderedClassElementsFixer::class,
        ParamReturnAndVarTagMalformsFixer::class,
        PhpdocLineSpanFixer::class,
        PhpdocNoEmptyReturnFixer::class,
        PhpdocOrderByValueFixer::class,
        PhpdocTypesCommaSpacesFixer::class,
        PhpdocVarAnnotationCorrectOrderFixer::class,
        PhpUnitAssertArgumentsOrderFixer::class,
        PhpUnitDataProviderReturnTypeFixer::class,
        PhpUnitDataProviderStaticFixer::class,
        PhpUnitDedicateAssertInternalTypeFixer::class,
        PhpUnitExpectationFixer::class,
        PhpUnitMockFixer::class,
        PhpUnitNamespacedFixer::class,
        PhpUnitNoExpectationAnnotationFixer::class,
        RegularCallableCallFixer::class,
        ReturnAssignmentFixer::class,
        RequireCombinedAssignmentOperatorSniff::class,
        SelfStaticAccessorFixer::class,
        SetDefinitionCommandSniff::class,
        SingleLineConfigureCommandFixer::class,
        StaticLambdaFixer::class,
        StrictComparisonFixer::class,
        StrictParamFixer::class,
        TernaryToNullCoalescingFixer::class,
        TypeHintOrderFixer::class,
        UnusedInheritedVariablePassedToClosureSniff::class,
        UseArrowFunctionsFixer::class,
        UselessAliasSniff::class,
        UselessConstantTypeHintSniff::class,
        UselessParenthesesSniff::class,
        UselessVariableSniff::class,
        UseSprintfInExceptionsSniff::class,
        VoidReturnFixer::class,
    ])
    ->withConfiguredRule(BlankLineBeforeStatementFixer::class, ['statements' => ['do', 'for', 'foreach', 'return', 'switch', 'throw', 'try', 'while']])
    ->withConfiguredRule(DuplicateSpacesSniff::class, ['ignoreSpacesInAnnotation' => true])
    ->withConfiguredRule(EchoTagSyntaxFixer::class, ['format' => 'short'])
    ->withConfiguredRule(GeneralPhpdocAnnotationRemoveFixer::class, ['annotations' => ['author', 'inheritdoc']])
    ->withConfiguredRule(ListSyntaxFixer::class, ['syntax' => 'short'])
    ->withConfiguredRule(MultilinePromotedPropertiesFixer::class, ['minimum_number_of_parameters' => 2])
    ->withConfiguredRule(MultilineWhitespaceBeforeSemicolonsFixer::class, ['strategy' => 'new_line_for_chained_calls'])
    ->withConfiguredRule(NativeConstantInvocationFixer::class, ['fix_built_in' => false, 'include' => ['DIRECTORY_SEPARATOR', 'PHP_SAPI', 'PHP_VERSION_ID'], 'scope' => 'namespaced'])
    ->withConfiguredRule(NoWhitespaceBeforeCommaInArrayFixer::class, ['after_heredoc' => true])
    ->withConfiguredRule(NullableTypeDeclarationForDefaultNullValueFixer::class, ['use_nullable_type_declaration' => true])
    ->withConfiguredRule(PhpdocSeparationFixer::class, ['groups' => [['template', 'mixin'], ['preserveGlobalState', 'runInSeparateProcess'], ['copyright', 'license'], ['Attributes', 'Attribute'], ['ORM\\*'], ['Assert\\*']]])
    ->withConfiguredRule(PhpdocToCommentFixer::class, ['ignored_tags' => ['todo', 'see']])
    ->withConfiguredRule(PhpdocTypesFixer::class, ['groups' => ['simple', 'meta']])
    ->withConfiguredRule(PhpUnitTestCaseStaticMethodCallsFixer::class, ['call_type' => 'this'])
    ->withConfiguredRule(RandomApiMigrationFixer::class, ['replacements' => ['mt_rand' => 'random_int', 'rand' => 'random_int']])
    ->withConfiguredRule(ReferenceUsedNamesOnlySniff::class, ['searchAnnotations' => true, 'allowFullyQualifiedNameForCollidingClasses' => true, 'allowFullyQualifiedGlobalClasses' => true, 'allowFullyQualifiedGlobalFunctions' => true, 'allowFullyQualifiedGlobalConstants' => true, 'allowPartialUses' => false])
    ->withConfiguredRule(StringImplicitBackslashesFixer::class, ['single_quoted' => 'ignore', 'double_quoted' => 'escape', 'heredoc' => 'escape'])
    ->withConfiguredRule(TrailingCommaInMultilineFixer::class, ['elements' => ['arrays', 'arguments', 'match', 'parameters'], 'after_heredoc' => true])
    ->withConfiguredRule(UnusedUsesSniff::class, ['searchAnnotations' => true])
    ->withConfiguredRule(UnusedVariableSniff::class, ['ignoreUnusedValuesWhenOnlyKeysAreUsedInForeach' => true])
;
