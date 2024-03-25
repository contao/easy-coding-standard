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
use PhpCsFixer\DocBlock\DocBlock;
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
            'Comments should be between 74 and 86 characters long per line.',
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
        return $tokens->isAnyTokenKindsFound([T_COMMENT, T_DOC_COMMENT]);
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
            if ($tokens[$index]->isGivenKind(T_COMMENT)) {
                $index = $this->handleComment($tokens, $index);
            } elseif ($tokens[$index]->isGivenKind(T_DOC_COMMENT)) {
                $index = $this->handleDocComment($tokens, $index);
            }
        }
    }

    private function handleComment(Tokens $tokens, int $index): int
    {
        $content = $tokens[$index]->getContent();

        if (!str_starts_with($content, '// ')) {
            return $index + 1;
        }

        // Ignore comments that are on the same line as the code
        if (!str_contains($tokens[$index - 1]->getContent(), "\n")) {
            return $index + 1;
        }

        $end = $index;
        $comment = substr($content, 3);

        while (true) {
            $next = $tokens->getNextNonWhitespace($end);

            if (null === $next || !$tokens[$next]->isGivenKind(T_COMMENT)) {
                break;
            }

            $content = $tokens[$next]->getContent();

            // Preserve lines that contain URLs or lists
            if (preg_match('#^// (https:|- |\d+\. )#', $content)) {
                return $next + 1;
            }

            $comment .= ' '.substr($content, 3);
            $end = $next;
        }

        $lines = $this->getLines($comment, 80, '//');

        if (substr_count((string) end($lines), ' ') < 2) {
            $lines = $this->getLines($comment, 86, '//');

            if (substr_count((string) end($lines), ' ') < 2) {
                $lines = $this->getLines($comment, 74, '//');
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

        return $end + 1;
    }

    private function handleDocComment(Tokens $tokens, int $index): int
    {
        $text = null;
        $newLines = [];

        $docBlock = new DocBlock($tokens[$index]->getContent());
        $lines = $docBlock->getLines();
        $content = end($lines)->getContent();
        $indent = substr($content, 0, strpos($content, '*'));

        foreach ($lines as $line) {
            $content = $line->getContent();

            // Preserve lines that contain URLs, lists or indented content
            if ($line->containsATag() || !$line->containsUsefulContent() || preg_match('#^ *\* ( |https:|- |\d+\. )#', $content)) {
                if ($text) {
                    $comment = rtrim($text);
                    $lns = $this->getLines($comment, 80, '*');

                    if (substr_count((string) end($lns), ' ') < 2) {
                        $lns = $this->getLines($comment, 86, '*');

                        if (substr_count((string) end($lns), ' ') < 2) {
                            $lns = $this->getLines($comment, 74, '*');
                        }
                    }

                    foreach ($lns as $ln) {
                        $newLines[] = "$indent$ln\n";
                    }

                    $text = null;
                }

                $newLines[] = $content;
                continue;
            }

            $text .= rtrim(substr($content, \strlen($indent) + 2)).' ';
        }

        $tokens->offsetSet($index, new Token([T_DOC_COMMENT, implode('', $newLines)]));

        return $index + 1;
    }

    private function getLines(string $comment, int $length, string $prefix = ''): array
    {
        $lines = [];
        $i = 0;
        $chunks = explode(' ', $comment);

        while ([] !== $chunks) {
            $word = array_shift($chunks);

            if (!isset($lines[$i])) {
                $lines[$i] = $prefix;
            }

            if ($prefix !== $lines[$i] && \strlen($lines[$i]) + \strlen($word) > $length) {
                $lines[++$i] = $prefix;
            }

            $lines[$i] .= " $word";
        }

        return $lines;
    }
}
