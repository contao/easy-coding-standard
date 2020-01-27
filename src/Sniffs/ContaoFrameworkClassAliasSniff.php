<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;

final class ContaoFrameworkClassAliasSniff implements Sniff
{
    public function register(): array
    {
        return [T_STRING];
    }

    public function process(File $file, $index)
    {
        $tokens = $file->getTokens();

        if (T_STRING !== $tokens[$index]['code'] || !$this->isContaoClass($tokens, $index)) {
            return;
        }

        if (T_NS_SEPARATOR !== $tokens[$index - 1]['code'] && $this->isNamespaced($file)) {
            return;
        }

        if (T_NS_SEPARATOR === $tokens[$index - 1]['code'] && 'Contao' === $tokens[$index - 2]['content']) {
            return;
        }

        if ($this->hasUse($file, 'Contao\\'.$tokens[$index]['content'])) {
            return;
        }

        $file->addError(sprintf('Using the aliased class "%1$s" is deprecated. Use the original class "Contao\%1$s" instead.', $tokens[$index]['content']), $index, self::class);
    }

    private function isContaoClass(array $tokens, int $index): bool
    {
        if (!\in_array($tokens[$index + 1]['code'], [T_OPEN_PARENTHESIS, T_DOUBLE_COLON], true)) {
            return false;
        }

        if (!preg_match('/^[A-Z]/', $tokens[$index]['content'])) {
            return false;
        }

        // Skip fully qualified class names
        if (T_NS_SEPARATOR === $tokens[$index - 1]['code'] && T_STRING === $tokens[$index - 2]['code']) {
            return false;
        }

        if (!class_exists('Contao\\'.$tokens[$index]['content'])) {
            return false;
        }

        return true;
    }

    private function isNamespaced(File $file): bool
    {
        $end = TokenHelper::findNext($file, T_USE, 0);

        return (bool) TokenHelper::findNext($file, T_NAMESPACE, 0, $end);
    }

    private function hasUse(File $file, string $class): bool
    {
        $end = TokenHelper::findNext($file, [T_CLASS, T_INTERFACE, T_TRAIT], 0);
        $uses = TokenHelper::findNextAll($file, T_USE, 0, $end);

        foreach ($uses as $use) {
            $end = TokenHelper::findNext($file, T_SEMICOLON, $use + 2);
            $fqcn = TokenHelper::getContent($file, $use + 2, $end - 1);

            if ($fqcn === $class) {
                return true;
            }
        }

        return false;
    }
}
