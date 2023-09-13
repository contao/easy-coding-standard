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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class NoLineBreakBetweenMethodArgumentsFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    private static array $cppKinds = [
        CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC,
        CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED,
        CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE,
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Method declarations must be done in a single line.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php
                        class Foo
                        {
                            public function bar(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): void
                            {
                            }
                        }
                        EOT,
                ),
            ],
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
            $isConstructor = '__construct' === $tokens[$nextMeaningful]->getContent();

            if (!$isLambda) {
                $nextMeaningful = $tokens->getNextMeaningfulToken($nextMeaningful);
            }

            if (!$end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextMeaningful)) {
                continue;
            }

            $isPropertyPromotion = false;

            if ($isConstructor) {
                for ($i = $nextMeaningful; $i < $end; ++$i) {
                    if ($tokens[$i]->isGivenKind(self::$cppKinds)) {
                        $isPropertyPromotion = true;
                        break;
                    }
                }
            }

            if ($isPropertyPromotion) {
                $index = $end + 1;
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
                continue;
            }

            $bodyStart = $tokens->getNextTokenOfKind($index, ['{', ';']);

            // The method is an abstract method
            if (!$bodyStart || $tokens[$bodyStart]->equals(';')) {
                continue;
            }

            // Insert a line-break before the opening curly brace
            if (!str_contains($tokens[$bodyStart - 1]->getContent(), "\n")) {
                $tokens->offsetSet($bodyStart - 1, new Token([T_WHITESPACE, $this->getIndent($tokens, $index)]));
            }
        }
    }
}
