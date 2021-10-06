<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__.'/set/contao.php');

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PARALLEL, true);

    $parameters->set(Option::SKIP, [
        '*/Resources/*',
        '*/Fixtures/system/*',
        MethodChainingIndentationFixer::class => [
            '*/DependencyInjection/Configuration.php',
        ],
        // TODO: remove again once PHP 8 attributes are supported
        NativeConstantInvocationFixer::class,
        UnusedVariableSniff::class => [
            'core-bundle/tests/Session/Attribute/ArrayAttributeBagTest.php',
        ],
    ]);

    $parameters->set(Option::LINE_ENDING, "\n");
    $parameters->set(Option::CACHE_DIRECTORY, sys_get_temp_dir().'/ecs_default_cache');
};
