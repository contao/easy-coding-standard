<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\ModifierKeywordsFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleTraitInsertPerStatementFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\StringNotation\NoTrailingWhitespaceInStringFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBetweenImportGroupsFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([
        __DIR__.'/psr1.php',
        __DIR__.'/psr2.php',
    ])
    ->withRules([
        BlankLineAfterOpeningTagFixer::class,
        BlankLineBetweenImportGroupsFixer::class,
        BlankLinesBeforeNamespaceFixer::class,
        CompactNullableTypeDeclarationFixer::class,
        DeclareEqualNormalizeFixer::class,
        LowercaseCastFixer::class,
        LowercaseStaticReferenceFixer::class,
        ModifierKeywordsFixer::class,
        NoBlankLinesAfterClassOpeningFixer::class,
        NoLeadingImportSlashFixer::class,
        NoTrailingWhitespaceInStringFixer::class,
        NoUnreachableDefaultArgumentValueFixer::class,
        NoWhitespaceInBlankLineFixer::class,
        ReturnTypeDeclarationFixer::class,
        ShortScalarCastFixer::class,
        SingleSpaceAroundConstructFixer::class,
        SingleTraitInsertPerStatementFixer::class,
        TernaryOperatorSpacesFixer::class,
    ])
    ->withConfiguredRule(BinaryOperatorSpacesFixer::class, ['default' => 'at_least_single_space'])
    ->withConfiguredRule(BracesPositionFixer::class, ['allow_single_line_anonymous_functions' => false, 'allow_single_line_empty_anonymous_classes' => true])
    ->withConfiguredRule(ClassDefinitionFixer::class, ['inline_constructor_arguments' => false, 'space_before_parenthesis' => true])
    ->withConfiguredRule(NewWithParenthesesFixer::class, ['anonymous_class' => true])
    ->withConfiguredRule(NoExtraBlankLinesFixer::class, ['tokens' => 'use'])
    ->withConfiguredRule(OrderedClassElementsFixer::class, ['order' => ['use_trait']])
    ->withConfiguredRule(OrderedImportsFixer::class, ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'none'])
    ->withConfiguredRule(SingleImportPerStatementFixer::class, ['group_to_single_imports' => false])
    ->withConfiguredRule(SingleSpaceAroundConstructFixer::class, ['constructs_followed_by_a_single_space' => ['abstract', 'as', 'case', 'catch', 'class', 'const_import', 'do', 'else', 'elseif', 'final', 'finally', 'for', 'foreach', 'function', 'function_import', 'if', 'insteadof', 'interface', 'namespace', 'new', 'private', 'protected', 'public', 'static', 'switch', 'trait', 'try', 'use', 'use_lambda', 'while'], 'constructs_preceded_by_a_single_space' => ['as', 'else', 'elseif', 'use_lambda']])
    ->withConfiguredRule(UnaryOperatorSpacesFixer::class, ['only_dec_inc' => true])
;
