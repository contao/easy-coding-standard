<?php

declare(strict_types=1);

use Contao\EasyCodingStandard\Fixer\AssertEqualsFixer;
use Contao\EasyCodingStandard\Fixer\ExpectsWithCallbackFixer;
use Contao\EasyCodingStandard\Fixer\InlinePhpdocCommentFixer;
use Contao\EasyCodingStandard\Fixer\IsArrayNotEmptyFixer;
use Contao\EasyCodingStandard\Fixer\MockMethodChainingIndentationFixer;
use Contao\EasyCodingStandard\Fixer\MultiLineIfIndentationFixer;
use Contao\EasyCodingStandard\Fixer\MultiLineLambdaFunctionArgumentsFixer;
use Contao\EasyCodingStandard\Fixer\NoExpectsThisAnyFixer;
use Contao\EasyCodingStandard\Fixer\NoLineBreakBetweenMethodArgumentsFixer;
use Contao\EasyCodingStandard\Fixer\SingleLineConfigureCommandFixer;
use Contao\EasyCodingStandard\Sniffs\ContaoFrameworkClassAliasSniff;
use Contao\EasyCodingStandard\Sniffs\SetDefinitionCommandSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\VersionControl\GitMergeConflictSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\LanguageConstructSpacingSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\SuperfluousWhitespaceSniff;
use PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer;
use PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer;
use PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer;
use PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\FunctionNotation\CombineNestedDirnameFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\StaticLambdaFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NoUnsetOnPropertyFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\Operator\LogicalOperatorsFixer;
use PhpCsFixer\Fixer\Operator\TernaryToElvisOperatorFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderByValueFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer;
use PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMockFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitNamespacedFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitNoExpectationAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitSetUpTearDownVisibilityFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\EscapeImplicitBackslashesFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer;
use PhpCsFixer\Fixer\StringNotation\NoBinaryStringFixer;
use PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\StringLineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypehintFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $vendorDir = __DIR__.'/../../vendor';

    if (!is_dir($vendorDir)) {
        $vendorDir = __DIR__.'/../../../..';
    }

    $containerConfigurator->import($vendorDir.'/symplify/easy-coding-standard/config/set/symfony.php');
    $containerConfigurator->import($vendorDir.'/symplify/easy-coding-standard/config/set/symfony-risky.php');

    $services = $containerConfigurator->services();

    $services
        ->set(BlankLineBeforeStatementFixer::class)
        ->call('configure', [[
            //  Enforce blank lines everywhere except befor "break", "continue" and "yield"
            'statements' => ['case', 'declare', 'default', 'do', 'for', 'foreach', 'if', 'return', 'switch', 'throw', 'try', 'while'],
        ]])
    ;

    $services
        ->set(EchoTagSyntaxFixer::class)
        ->call('configure', [[
            'format' => 'short',
        ]])
    ;

    $services
        ->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => "This file is part of Contao.\n\n(c) Leo Feyer\n\n@license LGPL-3.0-or-later",
        ]])
    ;

    $services
        ->set(ListSyntaxFixer::class)
        ->call('configure', [[
            'syntax' => 'short',
        ]])
    ;

    $services
        ->set(MultilineWhitespaceBeforeSemicolonsFixer::class)
        ->call('configure', [[
            'strategy' => 'new_line_for_chained_calls',
        ]])
    ;

    $services
        ->set(PhpdocTypesFixer::class)
        ->call('configure', [[
            'groups' => ['simple', 'meta'],
        ]])
    ;

    $services
        ->set(PhpUnitTestCaseStaticMethodCallsFixer::class)
        ->call('configure', [[
            'call_type' => 'this',
        ]])
    ;

    $services
        ->set(RandomApiMigrationFixer::class)
        ->call('configure', [[
            'mt_rand' => 'random_int',
            'rand' => 'random_int',
        ]])
    ;

    $services
        ->set(SuperfluousWhitespaceSniff::class)
        ->property('ignoreBlankLines', false)
    ;

    $services->set(AlignMultilineCommentFixer::class);
    $services->set(ArrayIndentationFixer::class);
    $services->set(BlankLineAfterStrictTypesFixer::class);
    $services->set(CombineConsecutiveIssetsFixer::class);
    $services->set(CombineConsecutiveUnsetsFixer::class);
    $services->set(CombineNestedDirnameFixer::class);
    $services->set(CompactNullableTypehintFixer::class);
    $services->set(DeclareStrictTypesFixer::class);
    $services->set(EscapeImplicitBackslashesFixer::class);
    $services->set(FullyQualifiedStrictTypesFixer::class);
    $services->set(GitMergeConflictSniff::class);
    $services->set(HeredocToNowdocFixer::class);
    $services->set(LanguageConstructSpacingSniff::class);
    $services->set(LinebreakAfterOpeningTagFixer::class);
    $services->set(LogicalOperatorsFixer::class);
    $services->set(MethodChainingIndentationFixer::class);
    $services->set(MultilineCommentOpeningClosingFixer::class);
    $services->set(NoAlternativeSyntaxFixer::class);
    $services->set(NoBinaryStringFixer::class);
    $services->set(NoNullPropertyInitializationFixer::class);
    $services->set(NoSuperfluousElseifFixer::class);
    $services->set(NoUnreachableDefaultArgumentValueFixer::class);
    $services->set(NoUnsetCastFixer::class);
    $services->set(NoUnsetOnPropertyFixer::class);
    $services->set(NoUselessElseFixer::class);
    $services->set(NoUselessReturnFixer::class);
    $services->set(OrderedClassElementsFixer::class);
    $services->set(ParamReturnAndVarTagMalformsFixer::class);
    $services->set(PhpdocLineSpanFixer::class);
    $services->set(PhpdocNoEmptyReturnFixer::class);
    $services->set(PhpdocOrderFixer::class);
    $services->set(PhpdocVarAnnotationCorrectOrderFixer::class);
    $services->set(PhpUnitDedicateAssertInternalTypeFixer::class);
    $services->set(PhpUnitExpectationFixer::class);
    $services->set(PhpUnitMethodCasingFixer::class);
    $services->set(PhpUnitMockFixer::class);
    $services->set(PhpUnitNamespacedFixer::class);
    $services->set(PhpUnitNoExpectationAnnotationFixer::class);
    $services->set(PhpdocOrderByValueFixer::class);
    $services->set(PhpUnitSetUpTearDownVisibilityFixer::class);
    $services->set(PhpUnitTestAnnotationFixer::class);
    $services->set(ProtectedToPrivateFixer::class);
    $services->set(ReturnAssignmentFixer::class);
    $services->set(SimpleToComplexStringVariableFixer::class);
    $services->set(StaticLambdaFixer::class);
    $services->set(StrictComparisonFixer::class);
    $services->set(StrictParamFixer::class);
    $services->set(StringLineEndingFixer::class);
    $services->set(TernaryToElvisOperatorFixer::class);
    $services->set(TernaryToNullCoalescingFixer::class);
    $services->set(VoidReturnFixer::class);

    // Add custom fixers
    $services->set(AssertEqualsFixer::class);
    $services->set(ExpectsWithCallbackFixer::class);
    $services->set(InlinePhpdocCommentFixer::class);
    $services->set(IsArrayNotEmptyFixer::class);
    $services->set(MockMethodChainingIndentationFixer::class);
    $services->set(MultiLineIfIndentationFixer::class);
    $services->set(MultiLineLambdaFunctionArgumentsFixer::class);
    $services->set(NoExpectsThisAnyFixer::class);
    $services->set(NoLineBreakBetweenMethodArgumentsFixer::class);
    $services->set(SingleLineConfigureCommandFixer::class);
    $services->set(ContaoFrameworkClassAliasSniff::class);
    $services->set(SetDefinitionCommandSniff::class);
};
