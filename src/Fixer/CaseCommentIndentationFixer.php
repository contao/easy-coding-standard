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

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class CaseCommentIndentationFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Comments before a "case" statement must be properly indented.',
            [
                new CodeSample(
                    '<?php

switch {
    // First case
    case 1:
        break;

    // Second case
    case 2:
        break;
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_CASE, T_DEFAULT]);
    }

    public function getPriority(): int
    {
        // must be run after StatementIndentationFixer
        return -10;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind([T_CASE, T_DEFAULT])) {
                continue;
            }

            $prevMeaningful = $tokens->getPrevNonWhitespace($index);

            if (!$tokens[$prevMeaningful]->isGivenKind(T_COMMENT)) {
                continue;
            }

            // If there is more than one line break between the comment and the
            // "case" statement, the two do not belong to each other.
            if (substr_count($tokens->generatePartialCode($prevMeaningful, $index), "\n") > 1) {
                continue;
            }

            $indentCase = $this->getIndent($tokens, $index);
            $indentComment = $this->getIndent($tokens, $prevMeaningful, false);

            if ($indentCase === $indentComment) {
                continue;
            }

            $indent = ltrim($indentCase, "\n");
            $replacement = rtrim($indentComment, "\t ").$indent;
            $i = $prevMeaningful - 1;

            $tokens->offsetSet($prevMeaningful - 1, new Token([T_WHITESPACE, $replacement]));

            // Handle multi-line comments
            while (true) {
                $prevComment = $tokens->getPrevNonWhitespace($i);

                if (!$tokens[$prevComment]->isGivenKind(T_COMMENT)) {
                    break;
                }

                $indentComment = $this->getIndent($tokens, $prevComment, false);
                $replacement = rtrim($indentComment, "\t ").$indent;
                $i = $prevComment - 1;

                $tokens->offsetSet($prevComment - 1, new Token([T_WHITESPACE, $replacement]));
            }
        }
    }
}
