<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\EasyCodingStandard\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class SetDefinitionCommandSniff implements Sniff
{
    private bool $isConfigure = false;

    public function register(): array
    {
        return [T_CLASS, T_FUNCTION, T_OBJECT_OPERATOR];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        switch (true) {
            case T_CLASS === $tokens[$stackPtr]['code']:
                if (!str_ends_with((string) $tokens[$stackPtr + 2]['content'], 'Command')) {
                    return \count($tokens) + 1;
                }
                break;

            case T_FUNCTION === $tokens[$stackPtr]['code']:
                $this->isConfigure = 'configure' === $tokens[$stackPtr + 2]['content'];
                break;

            case T_OBJECT_OPERATOR === $tokens[$stackPtr]['code']:
                if ($this->isConfigure && 'setDefinition' === $tokens[$stackPtr + 1]['content']) {
                    $phpcsFile->addError('Do not use the setDefinition() method to configure commands. Use addArgument() and addOption() instead.', $stackPtr, self::class);
                }
                break;
        }

        return \count($tokens) + 1;
    }
}
