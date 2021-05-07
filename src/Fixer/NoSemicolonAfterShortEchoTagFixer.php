<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class NoSemicolonAfterShortEchoTagFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Remove the semicolon after a short echo tag `<?= $this->foo; ?>` instruction.',
            [new CodeSample('<?= $this->foo; ?>\n')]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_OPEN_TAG_WITH_ECHO);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $hasShortEchoTag = false;

        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(T_OPEN_TAG_WITH_ECHO)) {
                $hasShortEchoTag = true;
            }

            if (!$token->isGivenKind(T_CLOSE_TAG)) {
                continue;
            }

            $prev = $tokens->getPrevMeaningfulToken($index);

            if ($hasShortEchoTag && $tokens[$prev]->equalsAny([';'])) {
                $tokens->clearAt($prev);
            }

            $hasShortEchoTag = false;
        }
    }
}
