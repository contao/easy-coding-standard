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

            $chainedCalls = 0;
            $operators = $tokens->findGivenKind(T_OBJECT_OPERATOR, $start, $end);

            foreach (array_keys($operators) as $pos) {
                if ($tokens[$pos - 1]->isWhitespace()) {
                    ++$chainedCalls;
                }
            }

            if ($chainedCalls < 1) {
                $index = $end;
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($end);

            if ($tokens[$nextMeaningful]->equals('}')) {
                $index = $end;
                continue;
            }

            if (substr_count($tokens[$end + 1]->getContent(), "\n") < 2) {
                $tokens->insertAt($end + 1, new Token([T_WHITESPACE, "\n"]));
            }

            $index = $end;
        }
    }
}
