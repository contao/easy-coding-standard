<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

use Contao\EasyCodingStandard\Fixer\CommentLengthFixer;
use Contao\EasyCodingStandard\Fixer\FindByPkFixer;
use Contao\EasyCodingStandard\Set\SetList;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([SetList::CONTAO])
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/ecs.php',
        __DIR__.'/rector.php',
    ])
    ->withSkip([
        CommentLengthFixer::class => [
            'config/contao.php',
        ],
        FindByPkFixer::class => [
            'src/Fixer/FindByPkFixer.php',
        ],
        ReferenceUsedNamesOnlySniff::class => [
            'config/contao.php',
        ],
    ])
    ->withConfiguredRule(HeaderCommentFixer::class, [
        'header' => "This file is part of Contao.\n\n(c) Leo Feyer\n\n@license LGPL-3.0-or-later",
    ])
    ->withParallel()
    ->withCache(sys_get_temp_dir().'/ecs/ecs')
;
