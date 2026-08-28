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
use PhpCsFixer\Tokenizer\Tokens;

final class IsArrayNotEmptyFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'When checking isset() and is_array() or is_array() and !empty(), the is_array() check should be last.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php

                        if (!empty($array) && is_array($array)) {
                        }
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php

                        if (isset($array) && is_array($array)) {
                        }
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_STRING)) {
                continue;
            }

            if ('is_array' !== $tokens[$index]->getContent() || !$tokens[$index + 1]->equals('(')) {
                continue;
            }

            $isArrayStart = $index;

            if ($tokens[$isArrayStart - 1]->isGivenKind(T_NS_SEPARATOR)) {
                --$isArrayStart;
            }

            $prev = $tokens->getPrevMeaningfulToken($isArrayStart);

            if (!$tokens[$prev]->isGivenKind(T_BOOLEAN_AND) && !$tokens[$prev]->equals('(')) {
                continue;
            }

            $isArrayArg = $index + 1;
            $isArrayEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $isArrayArg);
            $booleanAnd = $tokens->getNextMeaningfulToken($isArrayEnd);

            if (!$tokens[$booleanAnd]->isGivenKind(T_BOOLEAN_AND)) {
                continue;
            }

            $emptyStart = $tokens->getNextNonWhitespace($booleanAnd);

            if ($tokens[$emptyStart]->isGivenKind(T_ISSET)) {
                $empty = $emptyStart;
            } elseif ($tokens[$emptyStart]->equals('!')) {
                $empty = $tokens->getNextMeaningfulToken($emptyStart);

                if (!$tokens[$empty]->isGivenKind(T_EMPTY)) {
                    continue;
                }
            } else {
                continue;
            }

            $emptyArg = $empty + 1;
            $emptyEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $emptyArg);

            $isArrayContent = $tokens->generatePartialCode($isArrayArg + 1, $isArrayEnd - 1);
            $emptyContent = $tokens->generatePartialCode($emptyArg + 1, $emptyEnd - 1);

            if ($isArrayContent !== $emptyContent) {
                continue;
            }

            $newTokens = [];

            foreach ([[$emptyStart, $emptyEnd], [$isArrayEnd + 1, $emptyStart - 1], [$isArrayStart, $isArrayEnd]] as [$start, $end]) {
                for ($i = $start; $i <= $end; ++$i) {
                    $newTokens[] = $tokens[$i];
                }
            }

            $tokens->overrideRange($isArrayStart, $emptyEnd, $newTokens);

            $index = $emptyEnd + 1;
        }
    }
}
