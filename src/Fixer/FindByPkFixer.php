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

final class FindByPkFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'When searching models by ID, findById() should be used instead of findByPk().',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php

                        public function findModel(int $id): void
                        {
                            return Model::findById($id);
                        }
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_STRING, T_CONSTANT_ENCAPSED_STRING]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind([T_STRING, T_CONSTANT_ENCAPSED_STRING])) {
                continue;
            }

            $content = $tokens[$index]->getContent();

            if (!\in_array($content, ['findByPk', "'findByPk'", '"findByPk"'], true)) {
                continue;
            }

            if ($tokens[$index]->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
                $tokens->offsetSet($index, new Token([T_CONSTANT_ENCAPSED_STRING, "'findById'"]));
            } elseif ($this->isMethodCall($index, $tokens)) {
                $tokens->offsetSet($index, new Token([T_STRING, 'findById']));
            }
        }
    }

    private function isMethodCall(int $index, Tokens $tokens): bool
    {
        if (!$tokens[$index + 1]->equals('(')) {
            return false;
        }

        if (!$tokens[$index - 1]->isGivenKind([T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM])) {
            return false;
        }

        return !$tokens[$index - 2]->equals([T_STRING, 'parent']);
    }
}
