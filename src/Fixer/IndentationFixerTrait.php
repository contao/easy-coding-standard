<?php

declare(strict_types=1);

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
        } while (false === strpos($tokens[$index]->getContent(), "\n"));

        return "\n".ltrim($tokens[$index]->getContent(), "\n");
    }

    protected function isMultiLineStatement(Tokens $tokens, int $start, int $end): bool
    {
        $whitespaces = $tokens->findGivenKind(T_WHITESPACE, $start, $end);

        foreach ($whitespaces as $whitespace) {
            if (false !== strpos($whitespace->getContent(), "\n")) {
                return true;
            }
        }

        return false;
    }
}
