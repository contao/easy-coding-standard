<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\Tokenizer\Tokens;

trait IndentationFixerTrait
{
    protected function getIndent(Tokens $tokens, int $index): string
    {
        do {
            if (!$index = (int) $tokens->getPrevTokenOfKind($index, [[T_WHITESPACE]])) {
                return '';
            }
        } while (!str_contains($tokens[$index]->getContent(), "\n"));

        return "\n".ltrim($tokens[$index]->getContent(), "\n");
    }

    protected function isMultiLineStatement(Tokens $tokens, int $start, int $end): bool
    {
        $whitespaces = $tokens->findGivenKind(T_WHITESPACE, $start, $end);

        foreach ($whitespaces as $whitespace) {
            if (str_contains($whitespace->getContent(), "\n")) {
                return true;
            }
        }

        return false;
    }
}
