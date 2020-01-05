<?php

namespace Contao\EasyCodingStandard\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class SetDefinitionCommandSniff implements Sniff
{
    private $isConfigure = false;

    public function register(): array
    {
        return [T_CLASS, T_FUNCTION, T_OBJECT_OPERATOR];
    }

    public function process(File $file, $index)
    {
        $tokens = $file->getTokens();

        switch (true) {
            case T_CLASS === $tokens[$index]['code']:
                if ('Command' !== substr($tokens[$index + 2]['content'], -7)) {
                    return \count($tokens) + 1;
                }
                break;

            case T_FUNCTION === $tokens[$index]['code']:
                $this->isConfigure = 'configure' === $tokens[$index + 2]['content'];
                break;

            case T_OBJECT_OPERATOR === $tokens[$index]['code']:
                if ($this->isConfigure && 'setDefinition' === $tokens[$index + 1]['content']) {
                    $file->addError('Do not use the setDefinition() method to configure commands. Use addArgument() and addOption() instead.', $index, self::class);
                }
                break;
        }

        return \count($tokens) + 1;
    }
}
