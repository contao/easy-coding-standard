<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([SetList::PSR_12]);

    // @see \PhpCsFixer\RuleSet\Sets\PSR12Set
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\CompactNullableTypehintFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\SingleTraitInsertPerStatementFixer::class);

    // @see \PhpCsFixer\RuleSet\Sets\PSR12RiskySet
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\NoTrailingWhitespaceInStringFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer::class);

    // @see \PhpCsFixer\RuleSet\Sets\SymfonySet
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\BacktickToShellExecFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer::class, ['statements' => ['declare', 'default', 'do', 'for', 'foreach', 'if', 'return', 'switch', 'throw', 'try', 'while']]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Basic\BracesFixer::class, ['allow_single_line_anonymous_class_with_empty_body' => true, 'allow_single_line_closure' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\CastSpacesFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer::class, ['elements' => ['method' => 'one']]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer::class, ['single_line' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\ClassReferenceNameCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Operator\ConcatSpaceFixer::class, ['spacing' => 'none']);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ControlStructure\EmptyLoopBodyFixer::class, ['style' => 'braces']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\EmptyLoopConditionFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\IncludeFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\IncrementStyleFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\IntegerLiteralCaseFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\LambdaNotUsedImportFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\NativeFunctionTypeDeclarationCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\NoBinaryStringFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer::class, ['tokens' => ['curly_brace_block', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'throw', 'use']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer::class, ['allow_mixed' => true, 'allow_unused_params' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\NoTrailingCommaInListCallFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\NoTrailingCommaInSinglelineFunctionCallFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer::class, ['statements' => ['break', 'clone', 'continue', 'echo_print', 'return', 'switch_case', 'yield', 'yield_from']]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer::class, ['namespaces' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\NoUnneededImportAliasFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\NoUnusedImportsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoAccessFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoAliasTagFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoPackageFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\PhpdocTagTypeFixer::class, ['tags' => ['inheritdoc' => 'inline']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer::class, ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer::class, ['comment_types' => ['hash']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\SingleLineThrowFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer::class, ['remove_in_empty_for_expressions' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\SwitchContinueToBreakFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer::class);

    // @see \PhpCsFixer\RuleSet\Sets\SymfonyRiskySet
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\ArrayPushFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\CombineNestedDirnameFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\DirConstantFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\EregToPregFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\ErrorSuppressionFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\FopenFlagOrderFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\FunctionNotation\FopenFlagsFixer::class, ['b_mode' => false]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer::class, ['functions' => ['get_called_class', 'get_class', 'get_class_this', 'php_sapi_name', 'phpversion', 'pi']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\ImplodeCallFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\LogicalOperatorsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\ModernizeTypesCastingFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer::class, ['fix_built_in' => false, 'include' => ['DIRECTORY_SEPARATOR', 'PHP_SAPI', 'PHP_VERSION_ID'], 'scope' => 'namespaced']);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer::class, ['include' => ['@compiler_optimized'], 'scope' => 'namespaced', 'strict' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Naming\NoHomoglyphNamesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\NoUselessSprintfFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitConstructFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitMockShortWillReturnFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitSetUpTearDownVisibilityFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\SetTypeToCastFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\StringLengthToEmptyFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\StringLineEndingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\TernaryToElvisOperatorFixer::class);

    // Contao
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer::class);
    $ecsConfig->rule(\Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer::class);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff::class);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\PHP\DisallowDirectMagicInvokeCallSniff::class);
    $ecsConfig->ruleWithConfiguration(\SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff::class, ['ignoreSpacesInAnnotation' => true]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer::class, ['format' => 'short']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\EscapeImplicitBackslashesFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer::class, ['annotations' => ['author', 'inheritdoc']]);
    $ecsConfig->rule(\PHP_CodeSniffer\Standards\Generic\Sniffs\VersionControl\GitMergeConflictSniff::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer::class, ['import_classes' => false, 'import_constants' => false, 'import_functions' => false]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\HeredocIndentationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer::class);
    $ecsConfig->rule(\PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\LanguageConstructSpacingSniff::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer::class, ['syntax' => 'short']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer::class, ['strategy' => 'new_line_for_chained_calls']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\NoUnsetOnPropertyFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer::class, ['use_nullable_type_declaration' => false]);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer::class);
    $ecsConfig->rule(\Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocOrderByValueFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitMockFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitNamespacedFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitNoExpectationAnnotationFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer::class, ['call_type' => 'this']);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer::class, ['replacements' => ['mt_rand' => 'random_int', 'rand' => 'random_int']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\RegularCallableCallFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer::class);
    $ecsConfig->ruleWithConfiguration(\SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff::class, ['searchAnnotations' => true, 'allowFullyQualifiedNameForCollidingClasses' => true, 'allowFullyQualifiedGlobalClasses' => true, 'allowFullyQualifiedGlobalFunctions' => true, 'allowFullyQualifiedGlobalConstants' => true, 'allowPartialUses' => false]);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\Operators\RequireCombinedAssignmentOperatorSniff::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\SelfStaticAccessorFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\StaticLambdaFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Strict\StrictComparisonFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Strict\StrictParamFixer::class);
    $ecsConfig->ruleWithConfiguration(\PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\SuperfluousWhitespaceSniff::class, ['ignoreBlankLines' => false]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer::class, ['elements' => ['arrays', 'parameters'], 'after_heredoc' => true]);
    $ecsConfig->ruleWithConfiguration(\SlevomatCodingStandard\Sniffs\Classes\TraitUseSpacingSniff::class, ['linesCountAfterLastUse' => 1, 'linesCountAfterLastUseWhenLastInClass' => 0, 'linesCountBeforeFirstUse' => 0, 'linesCountBetweenUses' => 0]);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\Functions\UnusedInheritedVariablePassedToClosureSniff::class);
    $ecsConfig->ruleWithConfiguration(\SlevomatCodingStandard\Sniffs\Namespaces\UnusedUsesSniff::class, ['searchAnnotations' => true]);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\UseArrowFunctionsFixer::class);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\Namespaces\UselessAliasSniff::class);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\TypeHints\UselessConstantTypeHintSniff::class);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff::class);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer::class);

    // Custom fixers
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\AssertEqualsFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Sniffs\ContaoFrameworkClassAliasSniff::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\ExpectsWithCallbackFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\InlinePhpdocCommentFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\IsArrayNotEmptyFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\MockMethodChainingIndentationFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\MultiLineIfIndentationFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\MultiLineLambdaFunctionArgumentsFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\NoExpectsThisAnyFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\NoLineBreakBetweenMethodArgumentsFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\SingleLineConfigureCommandFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Sniffs\SetDefinitionCommandSniff::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\TypeHintOrderFixer::class);
    $ecsConfig->rule(\Contao\EasyCodingStandard\Sniffs\UseSprintfInExceptionsSniff::class);
};
