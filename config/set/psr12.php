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
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleTraitInsertPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\StringNotation\NoTrailingWhitespaceInStringFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBetweenImportGroupsFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([
        __DIR__.'/psr1.php',
        __DIR__.'/psr2.php',
    ])
    ->withRules([
        BinaryOperatorSpacesFixer::class,
        BlankLineAfterOpeningTagFixer::class,
        BlankLineBetweenImportGroupsFixer::class,
        BlankLinesBeforeNamespaceFixer::class,
        CompactNullableTypeDeclarationFixer::class,
        DeclareEqualNormalizeFixer::class,
        LowercaseCastFixer::class,
        LowercaseStaticReferenceFixer::class,
        NewWithParenthesesFixer::class,
        NoBlankLinesAfterClassOpeningFixer::class,
        NoLeadingImportSlashFixer::class,
        NoTrailingWhitespaceInStringFixer::class,
        NoUnreachableDefaultArgumentValueFixer::class,
        NoWhitespaceInBlankLineFixer::class,
        ReturnTypeDeclarationFixer::class,
        ShortScalarCastFixer::class,
        SingleTraitInsertPerStatementFixer::class,
        TernaryOperatorSpacesFixer::class,
        VisibilityRequiredFixer::class,
    ])
    ->withConfiguredRule(BracesPositionFixer::class, ['allow_single_line_empty_anonymous_classes' => true])
    ->withConfiguredRule(ClassDefinitionFixer::class, ['inline_constructor_arguments' => false, 'space_before_parenthesis' => true])
    ->withConfiguredRule(OrderedImportsFixer::class, ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'none'])
    ->withConfiguredRule(SingleImportPerStatementFixer::class, ['group_to_single_imports' => false])
;
