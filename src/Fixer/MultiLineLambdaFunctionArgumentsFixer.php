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
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class MultiLineLambdaFunctionArgumentsFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Multi-line lambda function arguments must be on their own line.',
            [
                new CodeSample(
                    '<?php

$array = array_map(
    static function ($i) {
        return $i;
    },
    $array
);
',
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_FUNCTION);
    }

    public function getPriority(): int
    {
        // must be run after MethodArgumentSpaceFixer
        return -3;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($index);

            // Not a lambda function
            if (!$tokens[$nextMeaningful]->equals('(')) {
                continue;
            }

            $prevMeaningful = $tokens->getPrevMeaningfulToken($index);

            if ($tokens[$prevMeaningful]->isGivenKind(T_STATIC)) {
                $prevMeaningful = $tokens->getPrevMeaningfulToken($prevMeaningful);
            }

            // Not inside a method call
            if (!\in_array($tokens[$prevMeaningful]->getContent(), ['(', ','], true)) {
                continue;
            }

            // The arguments are on separate lines already
            if ($this->hasNewline($tokens, $prevMeaningful + 1)) {
                continue;
            }

            $start = $tokens->getPrevTokenOfKind($index, ['(']);
            $end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $start);

            // No line-breaks required for inline lambda functions
            if (!$this->isMultiLineStatement($tokens, $start, $end)) {
                continue;
            }

            $this->fixIndentation($tokens, $start, $end, $this->getIndent($tokens, $prevMeaningful));

            $index = $end;
        }
    }

    private function hasNewline(Tokens $tokens, int $index): bool
    {
        return $tokens[$index]->isGivenKind(T_WHITESPACE) && str_contains($tokens[$index]->getContent(), "\n");
    }

    private function fixIndentation(Tokens $tokens, int $start, int &$end, string $indent): void
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();
        $argumentsIndexes = $argumentsAnalyzer->getArguments($tokens, $start, $end);

        foreach ($argumentsIndexes as $argumentStart => $argumentEnd) {
            if ($tokens[$argumentStart]->isGivenKind(T_WHITESPACE)) {
                ++$argumentStart;
            }

            if ($tokens[$argumentStart]->isGivenKind([T_STATIC, T_FUNCTION])) {
                $this->indentFunctionBody($tokens, $argumentStart, $argumentEnd);
            }

            if (!$tokens[$argumentEnd + 2]->isGivenKind(T_WHITESPACE)) {
                continue;
            }

            if ($tokens[$argumentEnd + 1]->equals(',')) {
                $tokens->offsetSet($argumentEnd + 2, new Token([T_WHITESPACE, $indent.'    ']));
            } elseif ($tokens[$argumentEnd + 1]->equals(')')) {
                $tokens->offsetSet($argumentEnd + 2, new Token([T_WHITESPACE, substr($indent, 0, -4)]));
            }
        }

        // Add a line-break after the opening parenthesis
        if (!$tokens[$start + 1]->isGivenKind(T_WHITESPACE)) {
            $tokens->insertAt($start + 1, new Token([T_WHITESPACE, $indent.'    ']));
            ++$end;
        }

        // Add a line-break before the closing parenthesis
        if (!$tokens[$end]->isGivenKind(T_WHITESPACE)) {
            $tokens->insertAt($end, new Token([T_WHITESPACE, $indent]));
            ++$end;
        }
    }

    private function indentFunctionBody(Tokens $tokens, int $argumentStart, int $argumentEnd): void
    {
        if (!$bodyStart = $tokens->getNextTokenOfKind($argumentStart, ['{'])) {
            return;
        }

        $whitespaces = $tokens->findGivenKind(T_WHITESPACE, $bodyStart, $argumentEnd);

        foreach ($whitespaces as $pos => $whitespace) {
            $ws = $whitespace->getContent();

            if (!str_contains($ws, "\n")) {
                continue;
            }

            $tokens->offsetSet($pos, new Token([T_WHITESPACE, $ws.'    ']));
        }
    }
}
