<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;

final class NoSemicolonAfterSingleEchoInstructionFixer extends AbstractFixer
{
    public function getDefinition()
    {
        return new FixerDefinition(
            'Remove the semicolon after a single echo `<?= $this->foo; ?>` instruction.',
            [new CodeSample('<?= $this->foo; ?>\n')]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $instructions = 0;
        $hasEchoToken = false;

        foreach ($tokens as $index => $token) {
            if ($token->equalsAny([';'])) {
                ++$instructions;
            }

            if ($token->isGivenKind(T_ECHO) || $token->isGivenKind(T_OPEN_TAG_WITH_ECHO)) {
                $hasEchoToken = true;
            }

            if (!$token->isGivenKind(T_CLOSE_TAG)) {
                continue;
            }

            $prev = $tokens->getPrevMeaningfulToken($index);

            if (1 === $instructions && $hasEchoToken && $tokens[$prev]->equalsAny([';'])) {
                $tokens->clearAt($prev);
            }

            $instructions = 0;
            $hasEchoToken = false;
        }
    }
}
