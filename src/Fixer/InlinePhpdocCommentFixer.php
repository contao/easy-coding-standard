<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class InlinePhpdocCommentFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Inline phpDoc comments should not be converted to regular comments.',
            [
                new CodeSample(
                    '<?php

public function testFoo(): void
{
    /** @var string $str */
    $str = (new Foo())->get();
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_COMMENT);
    }

    public function getPriority(): int
    {
        // must be run after PhpdocToCommentFixer
        return 24;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_COMMENT)) {
                continue;
            }

            $content = $tokens[$index]->getContent();

            if (0 !== strncmp($content, '/* @', 4) || false !== strpos($content, "\n")) {
                continue;
            }

            $tokens->offsetSet($index, new Token([T_DOC_COMMENT, '/** @'.substr($content, 4)]));
        }
    }
}
