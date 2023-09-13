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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class FunctionCallWithMultilineArrayFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    private static array $methods = [
        'with',
        'withConsecutive',
        'withAnyParameters',
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Multiline array arguments in function calls need to be on their own line unless the array is the only argument or the second of two arguments.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php
                        myFunction([
                            "foo" => "Foo",
                            "bar" => "Bar",
                        ]);
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php
                        myFunction($foo, [
                            "foo" => "Foo",
                            "bar" => "Bar",
                        ]);
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php
                        myFunction(
                            $foo,
                            [
                                "foo" => "Foo",
                                "bar" => "Bar",
                            ],
                            $bar
                        );
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
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
            if (!$tokens[$index]->isGivenKind(T_STRING)) {
                continue;
            }

            if (!$tokens[$index + 1]->equals('(')) {
                continue;
            }

            $start = $index + 1;
            $end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $start);

            if (!$this->isMultiLineStatement($tokens, $start, $end)) {
                $index = $end + 1;
                continue;
            }

            $argumentsAnalyzer = new ArgumentsAnalyzer();
            $argumentsIndexes = $argumentsAnalyzer->getArguments($tokens, $start, $end);

            if (1 === \count($argumentsIndexes)) {
                $index = $end + 1;
                continue;
            }

            if (!$this->hasMultilineArrayArgument($tokens, $argumentsIndexes)) {
                $index = $end + 1;
                continue;
            }

            if ($this->shouldRemoveLineBreaks($tokens, $argumentsIndexes, $tokens[$index]->getContent())) {
                $this->removeLineBreaks($tokens, $argumentsIndexes, $end);
            } else {
                $this->addLineBreaks($tokens, $argumentsIndexes, $start, $end);
            }
        }
    }

    private function hasMultilineArrayArgument(Tokens $tokens, array $argumentsIndexes): bool
    {
        foreach (array_keys($argumentsIndexes) as $start) {
            if ($this->isMultilineArrayArgument($tokens, $start)) {
                return true;
            }
        }

        return false;
    }

    private function isMultilineArrayArgument(Tokens $tokens, int $start): bool
    {
        if ($tokens[$start]->isGivenKind([T_ELLIPSIS, CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
            $index = $start;
        } elseif (!$index = $tokens->getNextMeaningfulToken($start)) {
            return false;
        }

        if ($tokens[$index]->isGivenKind(T_ELLIPSIS)) {
            ++$index;
        }

        if (!$tokens[$index]->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
            return false;
        }

        return str_starts_with($tokens[$index + 1]->getContent(), "\n");
    }

    private function shouldRemoveLineBreaks(Tokens $tokens, array $argumentsIndexes, string $method): bool
    {
        if (\in_array($method, self::$methods, true)) {
            return false;
        }

        if (2 !== \count($argumentsIndexes)) {
            return false;
        }

        [$arg1, $arg2] = array_keys($argumentsIndexes);

        if ($this->isMultilineArrayArgument($tokens, $arg1)) {
            return false;
        }

        $firstArg = $tokens->generatePartialCode($arg1, $argumentsIndexes[$arg1]);

        return 0 === substr_count(trim($firstArg), ' ') && $this->isMultilineArrayArgument($tokens, $arg2);
    }

    private function addLineBreaks(Tokens $tokens, array $argumentsIndexes, int $start, int $end): void
    {
        $indent = $this->getIndent($tokens, $start);

        $firstArg = null;
        $lastArg = $end;

        foreach ($argumentsIndexes as $argStart => $argEnd) {
            if ($tokens[$argStart - 1]->equals('(') && !str_starts_with($tokens[$argStart]->getContent(), "\n")) {
                $firstArg = $argStart;
            }

            if ($this->isMultilineArrayArgument($tokens, $argStart)) {
                if (' ' === $tokens[$argStart]->getContent()) {
                    $tokens->offsetSet($argStart, new Token([T_WHITESPACE, $indent]));
                } elseif (str_starts_with($tokens[$argStart]->getContent(), "\n")) {
                    continue;
                }

                $whitespaces = $tokens->findGivenKind(T_WHITESPACE, $argStart, $argEnd);

                foreach ($whitespaces as $pos => $whitespace) {
                    $content = $whitespace->getContent();

                    if (str_starts_with($content, "\n")) {
                        $tokens->offsetSet($pos, new Token([T_WHITESPACE, $content.'    ']));
                    }
                }
            } elseif (' ' === $tokens[$argStart]->getContent()) {
                $tokens->insertAt($argStart, new Token([T_WHITESPACE, $indent.'   ']));
                ++$lastArg;
            }
        }

        if ($firstArg) {
            $tokens->insertAt($firstArg, new Token([T_WHITESPACE, $indent.'    ']));
            ++$lastArg;
        }

        if ($tokens[$lastArg]->equals(')') && !str_starts_with($tokens[$lastArg - 1]->getContent(), "\n")) {
            $tokens->insertAt($lastArg, new Token([T_WHITESPACE, $indent]));
        }

        if (!$tokens[$lastArg]->equals(')')) {
            $tokens->insertAt($lastArg, new Token([T_STRING, ',']));
        }
    }

    private function removeLineBreaks(Tokens $tokens, array $argumentsIndexes, int $end): void
    {
        $firstArg = null;
        $lastArg = $end;

        foreach ($argumentsIndexes as $argStart => $argEnd) {
            if ($tokens[$argStart - 1]->equals('(') && str_starts_with($tokens[$argStart]->getContent(), "\n")) {
                $firstArg = $argStart;
            }

            if ($this->isMultilineArrayArgument($tokens, $argStart)) {
                if (' ' === $tokens[$argStart]->getContent()) {
                    continue;
                }

                if (str_starts_with($tokens[$argStart]->getContent(), "\n")) {
                    $tokens->offsetSet($argStart, new Token([T_WHITESPACE, ' ']));
                }

                $whitespaces = $tokens->findGivenKind(T_WHITESPACE, $argStart, $argEnd);

                foreach ($whitespaces as $pos => $whitespace) {
                    $content = $whitespace->getContent();

                    if (str_starts_with($content, "\n") && \strlen($content) > 4) {
                        $tokens->offsetSet($pos, new Token([T_WHITESPACE, substr($content, 0, -4)]));
                    }
                }
            }
        }

        if ($firstArg) {
            $tokens->clearAt($firstArg);
        }

        if ($tokens[$lastArg]->equals(')') && str_starts_with($tokens[$lastArg - 1]->getContent(), "\n")) {
            $tokens->clearAt($lastArg - 1);
        }

        if ($tokens[$lastArg - 2]->equals(',')) {
            $tokens->clearAt($lastArg - 2);
        }
    }
}
