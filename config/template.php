<?php

declare(strict_types=1);

use Contao\EasyCodingStandard\Fixer\NoSemicolonAfterShortEchoTagFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoShortEchoTagFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__.'/set/contao.php');

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SKIP, [
        BlankLineAfterOpeningTagFixer::class => null,
        DeclareStrictTypesFixer::class => null,
        HeaderCommentFixer::class => null,
        LinebreakAfterOpeningTagFixer::class => null,
        NoAlternativeSyntaxFixer::class => null,
        NoShortEchoTagFixer::class => null,
        ReferenceUsedNamesOnlySniff::class => null,
        SemicolonAfterInstructionFixer::class => null,
        StrictComparisonFixer::class => null,
        StrictParamFixer::class => null,
        VisibilityRequiredFixer::class => null,
        VoidReturnFixer::class => null,

        // Temporarily disable the fixer until https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/5225 has been merged
        TernaryOperatorSpacesFixer::class => null,
    ]);

    $parameters->set(Option::FILE_EXTENSIONS, ['html5']);
    $parameters->set(Option::CACHE_DIRECTORY, sys_get_temp_dir().'/ecs_template_cache');

    $services = $containerConfigurator->services();
    $services->set(NoSemicolonAfterShortEchoTagFixer::class);
};
