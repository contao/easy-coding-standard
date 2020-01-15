<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class NoLineBreakBetweenMethodArgumentsFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Method declarations must be done in a single line.',
            [
                new CodeSample(
                    '<?php

class Foo
{
    public function bar(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): void
    {
    }
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_FUNCTION);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($index);

            if ($tokens[$nextMeaningful]->isGivenKind(CT::T_RETURN_REF)) {
                $nextMeaningful = $tokens->getNextMeaningfulToken($nextMeaningful);
            }

            $isLambda = !$tokens[$nextMeaningful]->isGivenKind(T_STRING);

            if (!$isLambda) {
                $nextMeaningful = $tokens->getNextMeaningfulToken($nextMeaningful);
            }

            if (!$end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextMeaningful)) {
                continue;
            }

            for ($i = $nextMeaningful; $i < $end; ++$i) {
                if (!$tokens[$i]->isGivenKind(T_WHITESPACE)) {
                    continue;
                }

                if ($tokens[$i - 1]->equals('(') || $tokens[$i + 1]->equals(')')) {
                    $tokens->clearAt($i);
                } else {
                    $tokens->offsetSet($i, new Token([T_WHITESPACE, ' ']));
                }
            }

            $index = $end + 1;

            if ($isLambda) {
                return;
            }

            $bodyStart = $tokens->getNextTokenOfKind($index, ['{', ';']);

            // The method is an abstract method
            if (!$bodyStart || $tokens[$bodyStart]->equals(';')) {
                continue;
            }

            // Insert a line-break before the opening curly brace
            if (false === strpos($tokens[$bodyStart - 1]->getContent(), "\n")) {
                $tokens->offsetSet($bodyStart - 1, new Token([T_WHITESPACE, $this->getIndent($tokens, $index)]));
            }
        }
    }
}
