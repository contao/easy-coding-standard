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

final class CommentLengthFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Inline comments should be between 74 and 86 characters per line.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php

                        // This comment exceeds the maximum line length of 80 characters. It should be
                        // distributed accross two lines.
                        if (true) {
                        }
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_COMMENT);
    }

    /**
     * Must run after InlinePhpdocCommentFixer.
     */
    public function getPriority(): int
    {
        return 25;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_COMMENT)) {
                continue;
            }

            $content = $tokens[$index]->getContent();

            if (!str_starts_with($content, '// ')) {
                continue;
            }

            // Ignore comments that are on the same line as the code
            if (!str_contains($tokens[$index - 1]->getContent(), "\n")) {
                continue;
            }

            $end = $index;
            $comment = substr($content, 3);

            while (true) {
                $next = $tokens->getNextNonWhitespace($end);

                if (null === $next || !$tokens[$next]->isGivenKind(T_COMMENT)) {
                    break;
                }

                $content = $tokens[$next]->getContent();

                // Preserve lines that contain only a URL
                if (str_starts_with($content, '// https:')) {
                    continue 2;
                }

                $comment .= ' '.substr($content, 3);
                $end = $next;
            }

            $lines = $this->getLines($comment, 80);

            if (substr_count((string) end($lines), ' ') < 2) {
                $lines = $this->getLines($comment, 86);

                if (substr_count((string) end($lines), ' ') < 2) {
                    $lines = $this->getLines($comment, 74);
                }
            }

            $new = [];
            $indent = $this->getIndent($tokens, $index);

            for ($i = 0, $c = \count($lines); $i < $c; ++$i) {
                if ($i > 0) {
                    $new[] = new Token([T_WHITESPACE, $indent]);
                }

                $new[] = new Token([T_COMMENT, $lines[$i]]);
            }

            $tokens->clearRange($index, $end);
            $tokens->insertAt($index, $new);

            $index = $end + 1;
        }
    }

    private function getLines(string $comment, int $length): array
    {
        $lines = [];
        $i = 0;
        $chunks = explode(' ', $comment);

        while ([] !== $chunks) {
            $word = array_shift($chunks);

            if (!isset($lines[$i])) {
                $lines[$i] = '//';
            }

            if ('//' !== $lines[$i] && \strlen($lines[$i]) + \strlen($word) > $length) {
                $lines[++$i] = '//';
            }

            $lines[$i] .= " $word";
        }

        return $lines;
    }
}
