<?php

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class MultiLineIfIndentationFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'A multi-line if statement must be properly indented.',
            [
                new CodeSample(
                    '<?php

class Foo
{
    public function bar(array $array): void
    {
        if (
            isset($array["bar"])
            && is_array($array["bar"])
        ) {
            // do something
        }
    }
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_IF, T_ELSEIF]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind([T_IF, T_ELSEIF])) {
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($index);

            if (!$tokens[$nextMeaningful]->equals('(')) {
                continue;
            }

            $indent = $this->getIndent($tokens, $index);
            $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextMeaningful);

            if (!$this->isMultiLineStatement($tokens, $nextMeaningful, $index)) {
                continue;
            }

            // Add a line-break after the opening parenthesis
            if (!$tokens[$nextMeaningful + 1]->isGivenKind(T_WHITESPACE)) {
                $tokens->insertAt($nextMeaningful + 1, new Token([T_WHITESPACE, $indent.'    ']));
                ++$index;
            }

            // Add a line-break before the closing parenthesis
            if (!$tokens[$index - 1]->isGivenKind(T_WHITESPACE)) {
                $tokens->insertAt($index, new Token([T_WHITESPACE, $indent]));
                ++$index;
            }
        }
    }

    private function isMultiLineStatement(Tokens $tokens, int $start, int $end): bool
    {
        $whitespaces = $tokens->findGivenKind(T_WHITESPACE, $start, $end);

        foreach ($whitespaces as $whitespace) {
            if (false !== strpos($whitespace->getContent(), "\n")) {
                return true;
            }
        }

        return false;
    }

    private function getIndent(Tokens $tokens, int $index): string
    {
        if (!$whitespace = $tokens->getPrevTokenOfKind($index, [[T_WHITESPACE]])) {
            return '';
        }

        return "\n".ltrim($tokens[$whitespace]->getContent(), "\n");
    }
}
