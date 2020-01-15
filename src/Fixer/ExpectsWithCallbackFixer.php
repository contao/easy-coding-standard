<?php

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class ExpectsWithCallbackFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Unless there are several $this->callback() calls, there must not be a line break before the call.',
            [
                new CodeSample(
                    '<?php

public function testFoo(): void
{
    $foo = $this->createMock(Foo::class);
    $foo
        ->method("bar")
        ->with($this->callback(
            function () {
            }
        ));
}
'
                ),
                new CodeSample(
                    '<?php

public function testFoo(): void
{
    $foo = $this->createMock(Foo::class);
    $foo
        ->method("bar")
        ->with(
            $this->callback(
                function () {
                }
            ),
            $this->callback(
                function () {
                }
            )
        );
}
'
                ),
            ]
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

            if (
                'callback' !== $tokens[$index]->getContent()
                || !$tokens[$index - 1]->isGivenKind(T_OBJECT_OPERATOR)
                || !$tokens[$index + 1]->equals('(')
            ) {
                continue;
            }

            $start = $index - 1;

            // Find the parent method call
            do {
                if (!$start = (int) $tokens->getPrevTokenOfKind($start, ['('])) {
                    continue 2;
                }
            } while ('with' !== $tokens[$start - 1]->getContent());

            $end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $start);

            $argumentsAnalyzer = new ArgumentsAnalyzer();
            $argumentsIndexes = $argumentsAnalyzer->getArguments($tokens, $start, $end);

            $needsLineBreak = \count($argumentsIndexes) > 1;
            $hasLineBreak = $tokens[$start + 1]->isGivenKind(T_WHITESPACE);

            if ($needsLineBreak && !$hasLineBreak) {
                $this->addLineBreak($tokens, $argumentsIndexes, $start, $end);
            } elseif (!$needsLineBreak && $hasLineBreak) {
                $this->removeLineBreak($tokens, $start, $end);
            }

            $index = $end;
        }
    }

    private function addLineBreak(Tokens $tokens, array $argumentsIndexes, int $start, int $end): void
    {
        $indent = $this->getIndent($tokens, $start);

        foreach ($argumentsIndexes as $argStart => $argEnd) {
            if ($tokens[$argEnd + 1]->equals(',') && false === strpos($tokens[$argEnd + 2]->getContent(), "\n")) {
                $tokens->offsetSet($argEnd + 2, new Token([T_WHITESPACE, $indent]));
            }

            if (!$functions = $tokens->findGivenKind(T_FUNCTION, $argStart, $argEnd)) {
                continue;
            }

            $funcStart = $tokens->getNextTokenOfKind($argStart, ['{']);

            if (false === strpos($tokens[$funcStart + 1]->getContent(), "\n")) {
                $tokens->insertAt($funcStart, new Token([T_WHITESPACE, $indent]));
            }

            $funcEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, (int) $funcStart);

            if (false === strpos($tokens[$funcEnd + 1]->getContent(), "\n")) {
                $tokens->insertAt($funcEnd + 1, new Token([T_WHITESPACE, $indent]));
            }
        }

        $whitespaces = $tokens->findGivenKind(T_WHITESPACE, $start + 2, $end);

        foreach ($whitespaces as $i => $whitespace) {
            $content = $whitespace->getContent();

            if (false !== strpos($content, "\n")) {
                $tokens->offsetSet($i, new Token([T_WHITESPACE, $content.'    ']));
            }
        }

        $tokens->insertAt($start + 1, new Token([T_WHITESPACE, $indent.'    ']));

        if ($tokens[$end + 1]->equals(')')) {
            $tokens->insertAt($end + 1, new Token([T_WHITESPACE, $indent]));
        }
    }

    private function removeLineBreak(Tokens $tokens, int $start, int $end): void
    {
        $whitespaces = $tokens->findGivenKind(T_WHITESPACE, $start, $end);

        foreach ($whitespaces as $i => $whitespace) {
            $content = $whitespace->getContent();

            if (false !== strpos($content, "\n")) {
                $tokens->offsetSet($i, new Token([T_WHITESPACE, substr($content, 0, -4)]));
            }
        }

        $tokens->clearAt($start + 1);
        $tokens->clearAt($end - 1);
    }

    private function getIndent(Tokens $tokens, int $index): string
    {
        $whitespace = $index;

        do {
            if (!$whitespace = $tokens->getPrevTokenOfKind($whitespace, [[T_WHITESPACE]])) {
                return '';
            }
        } while (false === strpos($tokens[$whitespace]->getContent(), "\n"));

        return "\n".ltrim($tokens[$whitespace]->getContent(), "\n");
    }
}
