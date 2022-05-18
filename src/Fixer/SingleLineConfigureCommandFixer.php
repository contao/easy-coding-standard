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

final class SingleLineConfigureCommandFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Defining command arguments and options must be done in a single line.',
            [
                new CodeSample(
                    '<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SomeCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument("foo", InputArgument::REQUIRED, "The argument")
            ->addOption("bar", null, InputOption::VALUE_NONE, "The option");
    }
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([T_CLASS, T_FUNCTION, T_OBJECT_OPERATOR]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            switch (true) {
                case $tokens[$index]->isGivenKind(T_CLASS):
                    $nextMeaningful = $tokens->getNextMeaningfulToken($index);

                    // Return if the class is not a command
                    if (!str_ends_with($tokens[$nextMeaningful]->getContent(), 'Command')) {
                        return;
                    }
                    break;

                case $tokens[$index]->isGivenKind(T_FUNCTION):
                    $nextMeaningful = $tokens->getNextMeaningfulToken($index);

                    // Skip the method if it is not the configure() method
                    if ('configure' !== $tokens[$nextMeaningful]->getContent()) {
                        $nextMeaningful = $tokens->getNextMeaningfulToken($index);

                        if ($tokens[$nextMeaningful]->equals('(')) {
                            $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextMeaningful);
                        }
                    }
                    break;

                case $tokens[$index]->isGivenKind(T_OBJECT_OPERATOR):
                    $nextMeaningful = $tokens->getNextMeaningfulToken($index);

                    if (!\in_array($tokens[$nextMeaningful]->getContent(), ['addArgument', 'addOption'], true)) {
                        continue 2;
                    }

                    $blockStart = $nextMeaningful + 1;
                    $blockEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $blockStart);

                    if ($tokens[$blockStart + 1]->isGivenKind(T_WHITESPACE)) {
                        $tokens->clearAt(++$blockStart);
                    }

                    if ($tokens[$blockEnd - 1]->isGivenKind(T_WHITESPACE)) {
                        $tokens->clearAt(--$blockEnd);
                    }

                    for ($i = $blockStart + 1; $i < $blockEnd; ++$i) {
                        if ($tokens[$i]->isWhitespace()) {
                            $tokens->offsetSet($i, new Token([T_WHITESPACE, ' ']));
                        }
                    }
            }
        }
    }
}
