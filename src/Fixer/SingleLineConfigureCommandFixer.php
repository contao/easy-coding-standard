<?php

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;

final class SingleLineConfigureCommandFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Defining command arguments and options must be done in single line.',
            [
                new CodeSample(
                    '<?php

use Symfony\Component\Console\Command\Command;

class SomeCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                "bundles",
                null,
                InputOption::VALUE_NONE,
                "List all bundles or the bundle configuration of the given plugin"
            );
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
                    if ('Command' !== substr($tokens[$nextMeaningful]->getContent(), -7)) {
                        return;
                    }
                    break;

                case $tokens[$index]->isGivenKind(T_FUNCTION):
                    $nextMeaningful = $tokens->getNextMeaningfulToken($index);

                    // Skip the method if it is not the configure() method
                    if ('configure' !== $tokens[$nextMeaningful]->getContent()) {
                        $nextMeaningful = $tokens->getNextMeaningfulToken($index);

                        if ($tokens[$nextMeaningful]->isGivenKind('(')) {
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

                    for ($i = $blockStart; $i < $blockEnd; ++$i) {
                        if ($tokens[$i]->isWhitespace()) {
                            $tokens->clearAt($i);
                        }
                    }
            }
        }
    }
}
