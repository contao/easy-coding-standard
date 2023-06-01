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

return static function (ECSConfig $ecsConfig): void {
    // @see \PhpCsFixer\RuleSet\Sets\PSR1Set
    $ecsConfig->rule(\PhpCsFixer\Fixer\Basic\EncodingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer::class);

    // @see \PhpCsFixer\RuleSet\Sets\PSR2Set
    $ecsConfig->rule(\PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer::class);
    // The BracesFixer has been split in PHP-CS-Fixer 3.10, so do no longer include it
    // $ecsConfig->rule(\PhpCsFixer\Fixer\Basic\BracesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\ConstantCaseFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\ElseifFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\IndentationTypeFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\LineEndingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer::class, ['on_multiline' => 'ensure_fully_multiline']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\NoBreakCommentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\NoSpaceAroundDoubleColonFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\NoSpacesAfterFunctionNameFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\NoSpacesInsideParenthesisFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer::class, ['elements' => ['property']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\SingleLineAfterImportsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\SwitchCaseSemicolonToColonFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\SwitchCaseSpaceFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer::class, ['elements' => ['method', 'property']]);

    // @see \PhpCsFixer\RuleSet\Sets\PSR12Set
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\BlankLineBetweenImportGroupsFixer::class);
    // The BracesFixer has been split in PHP-CS-Fixer 3.10, so do no longer include it
    // $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Basic\BracesFixer::class, ['allow_single_line_anonymous_class_with_empty_body' => true]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer::class, ['inline_constructor_arguments' => false, 'space_before_parenthesis' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\CompactNullableTypehintFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\NewWithBracesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer::class, ['order' => ['use_trait']]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Import\OrderedImportsFixer::class, ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'none']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer::class, ['group_to_single_imports' => false]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\SingleTraitInsertPerStatementFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer::class);

    // @see \PhpCsFixer\RuleSet\Sets\PSR12RiskySet
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\NoTrailingWhitespaceInStringFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer::class);

    // @see \PhpCsFixer\RuleSet\Sets\SymfonySet
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\BacktickToShellExecFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer::class, ['statements' => ['return']]);
    // The BracesFixer has been split in PHP-CS-Fixer 3.10, so do no longer include it
    // $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Basic\BracesFixer::class, ['allow_single_line_anonymous_class_with_empty_body' => true, 'allow_single_line_closure' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\CastSpacesFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer::class, ['elements' => ['method' => 'one']]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer::class, ['single_line' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\ClassReferenceNameCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\ConcatSpaceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ControlStructure\EmptyLoopBodyFixer::class, ['style' => 'braces']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\EmptyLoopConditionFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer::class, ['import_classes' => false, 'import_constants' => false, 'import_functions' => false]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\IncludeFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\IncrementStyleFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\IntegerLiteralCaseFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\LambdaNotUsedImportFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer::class, ['on_multiline' => 'ignore']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Casing\NativeFunctionTypeDeclarationCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\NoAliasLanguageConstructCallFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\NoBinaryStringFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer::class, ['tokens' => ['attribute', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'switch', 'throw', 'use']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer::class, ['allow_mixed' => true, 'allow_unused_params' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer::class, ['statements' => ['break', 'clone', 'continue', 'echo_print', 'others', 'return', 'switch_case', 'yield', 'yield_from']]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer::class, ['namespaces' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\NoUnneededImportAliasFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\NoUnusedImportsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\NoUselessConcatOperatorFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\NoUselessNullsafeOperatorFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Import\OrderedImportsFixer::class, ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoAccessFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoAliasTagFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoPackageFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer::class, ['order' => ['param', 'return', 'throws']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\PhpdocTagTypeFixer::class, ['tags' => ['inheritdoc' => 'inline']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer::class, ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer::class, ['comment_types' => ['hash']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\SingleLineThrowFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAfterConstructFixer::class, ['constructs' => ['abstract', 'as', 'attribute', 'break', 'case', 'catch', 'class', 'clone', 'comment', 'const', 'const_import', 'continue', 'do', 'echo', 'else', 'elseif', 'enum', 'extends', 'final', 'finally', 'for', 'foreach', 'function', 'function_import', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'match', 'named_argument', 'namespace', 'new', 'open_tag_with_echo', 'php_doc', 'php_open', 'print', 'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'type_colon', 'use', 'use_lambda', 'use_trait', 'var', 'while', 'yield', 'yield_from']]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer::class, ['remove_in_empty_for_expressions' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\SwitchContinueToBreakFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer::class);

    // @see \PhpCsFixer\RuleSet\Sets\SymfonyRiskySet
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\ArrayPushFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\CombineNestedDirnameFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\DirConstantFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\EregToPregFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\ErrorSuppressionFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\FopenFlagOrderFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\FunctionNotation\FopenFlagsFixer::class, ['b_mode' => false]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\ImplodeCallFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\LogicalOperatorsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\CastNotation\ModernizeTypesCastingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer::class, ['include' => ['@compiler_optimized'], 'scope' => 'namespaced', 'strict' => true]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Naming\NoHomoglyphNamesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\NoPhp4ConstructorFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\NoUselessSprintfFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitConstructFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitMockShortWillReturnFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitSetUpTearDownVisibilityFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Basic\PsrAutoloadingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Alias\SetTypeToCastFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\StringLengthToEmptyFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\StringLineEndingFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Operator\TernaryToElvisOperatorFixer::class);

    // The BracesFixer has been split in PHP-CS-Fixer 3.10, so include the replacements instead
    // @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/4884
    // @see https://cs.symfony.com/doc/rules/basic/braces.html#warning
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\ControlStructureBracesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Basic\CurlyBracesPositionFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\ControlStructureContinuationPositionFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\DeclareParenthesesFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Basic\NoMultipleStatementsPerLineFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer::class);

    // Contao
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer::class);
    $ecsConfig->rule(\Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer::class, ['statements' => ['do', 'for', 'foreach', 'return', 'switch', 'throw', 'try', 'while']]);
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
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\HeredocIndentationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer::class);
    $ecsConfig->rule(\PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\LanguageConstructSpacingSniff::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer::class, ['syntax' => 'short']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer::class, ['minimum_number_of_parameters' => 2]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer::class, ['strategy' => 'new_line_for_chained_calls']);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer::class, ['fix_built_in' => false, 'include' => ['DIRECTORY_SEPARATOR', 'PHP_SAPI', 'PHP_VERSION_ID'], 'scope' => 'namespaced']);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\LanguageConstruct\NoUnsetOnPropertyFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer::class);
    $ecsConfig->rule(\PhpCsFixerCustomFixers\Fixer\NoUselessParenthesisFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer::class);
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff::class);
    $ecsConfig->rule(\Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocOrderByValueFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer::class, ['groups' => [['template', 'mixin'], ['preserveGlobalState', 'runInSeparateProcess'], ['copyright', 'license'], ['Attributes', 'Attribute'], ['ORM\\*'], ['Assert\\*']]]);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer::class, ['ignored_tags' => ['todo', 'see']]);
    $ecsConfig->rule(\PhpCsFixerCustomFixers\Fixer\PhpdocTypesCommaSpacesFixer::class);
    $ecsConfig->ruleWithConfiguration(\PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer::class, ['groups' => ['simple', 'meta']]);
    $ecsConfig->rule(\PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer::class);
    $ecsConfig->rule(\PhpCsFixerCustomFixers\Fixer\PhpUnitAssertArgumentsOrderFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer::class);
    $ecsConfig->rule(\PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer::class);
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
    $ecsConfig->rule(\Contao\EasyCodingStandard\Fixer\CaseCommentIndentationFixer::class);
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
