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

final class ChainedMethodBlockFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'A block of chained method calls must be followed by an empty line.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php

                        use PHPUnit\Framework\TestCase;

                        class SomeTest extends TestCase
                        {
                            public function testFoo(): void
                            {
                                $mock = $this->createMock(Foo::class);
                                $mock
                                    ->method('isFoo')
                                    ->willReturn(true)
                                ;

                                $mock
                                    ->method('isBar')
                                    ->willReturn(false)
                                ;

                                $mock->isFoo();
                        }
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_OBJECT_OPERATOR);
    }

    /**
     * Must run before StatementIndentationFixer.
     */
    public function getPriority(): int
    {
        return -4;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_OBJECT_OPERATOR)) {
                continue;
            }

            // Not a chained call
            if (!$tokens[$index - 1]->isWhitespace()) {
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($index);

            // Not a method call
            if (!$tokens[$nextMeaningful + 1]->equals('(')) {
                continue;
            }

            $end = $tokens->getNextTokenOfKind($index, [';']);

            if (!$tokens[$end - 1]->isWhitespace()) {
                $index = $end;
                continue;
            }

            $start = $tokens->getPrevTokenOfKind($index, [';', '{']);
            $nextMeaningful = $tokens->getNextMeaningfulToken($start);

            if ($tokens[$nextMeaningful]->equals('}')) {
                $index = $end;
                continue;
            }

            $chainedCalls = $this->getChainedCalls($tokens, $start, $end);

            if ($chainedCalls < 1) {
                $index = $end;
                continue;
            }

            $this->fixLeadingWhitespace($tokens, $start);

            $index = $end;
        }
    }

    private function getChainedCalls(Tokens $tokens, int $start, int $end): int
    {
        $chainedCalls = 0;
        $operators = $tokens->findGivenKind(T_OBJECT_OPERATOR, $start, $end);

        foreach (array_keys($operators) as $pos) {
            if ($tokens[$pos - 1]->isWhitespace()) {
                ++$chainedCalls;
            }
        }

        return $chainedCalls;
    }

    private function fixLeadingWhitespace(Tokens $tokens, int $start): void
    {
        $prevStart = $tokens->getPrevTokenOfKind($start, [';', '{']);

        if (null === $prevStart) {
            return;
        }

        $prevIndex = $prevStart;
        $prevVar = $this->getBlockVariable($tokens, $prevStart, $prevIndex);

        if (null === $prevVar) {
            return;
        }

        $addNewLine = false;
        $removeNewLine = false;

        if ($this->isMultiline($tokens, $prevIndex, $start)) {
            $addNewLine = true;
        } else {
            $var = $this->getBlockVariable($tokens, $start);
            $prevVar = $this->getBlockVariable($tokens, $prevStart);
            $next = $tokens->getNextNonWhitespace($start);

            if ($tokens[$next]->isGivenKind([T_COMMENT, T_DOC_COMMENT])) {
                $addNewLine = true;
            } elseif ($var === $prevVar) {
                $removeNewLine = true;
            } elseif ($prevVar && str_starts_with($prevVar, "$var->")) {
                $addNewLine = true;
            }
        }

        $content = $tokens[$start + 1]->getContent();

        if ($addNewLine && !$tokens[$start + 2]->isGivenKind(T_WHITESPACE)) {
            if (substr_count($content, "\n") < 2) {
                $tokens->offsetSet($start + 1, new Token([T_WHITESPACE, str_replace("\n", "\n\n", $content)]));
            }
        } elseif ($removeNewLine && substr_count($content, "\n") > 1) {
            $tokens->offsetSet($start + 1, new Token([T_WHITESPACE, str_replace("\n\n", "\n", $content)]));
        }
    }

    private function isMultiline(Tokens $tokens, int $start, int $end): bool
    {
        $lineBreaks = 0;
        $operators = $tokens->findGivenKind(T_WHITESPACE, $start, $end);

        foreach (array_keys($operators) as $pos) {
            if ($tokens[$pos]->isWhitespace() && str_contains($tokens[$pos]->getContent(), "\n")) {
                ++$lineBreaks;
            }
        }

        return $lineBreaks > 1;
    }

    private function getBlockVariable(Tokens $tokens, int $start, int &$prevIndex = 0): string|null
    {
        $index = $tokens->getNextMeaningfulToken($start);

        if (!$tokens[$index]->isGivenKind(T_VARIABLE)) {
            return null;
        }

        $prevIndex = $index;
        $var = $tokens[$index]->getContent();

        if ($tokens[$index + 1]->isObjectOperator()) {
            $var = $tokens->generatePartialCode($index, $index + 2);
        }

        return $var;
    }
}
