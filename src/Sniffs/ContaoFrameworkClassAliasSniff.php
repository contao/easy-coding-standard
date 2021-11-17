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

    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (T_STRING !== $tokens[$stackPtr]['code'] || !$this->isContaoClass($tokens, $stackPtr)) {
            return;
        }

        if (T_NS_SEPARATOR !== $tokens[$stackPtr - 1]['code'] && $this->isNamespaced($phpcsFile)) {
            return;
        }

        if (T_NS_SEPARATOR === $tokens[$stackPtr - 1]['code'] && 'Contao' === $tokens[$stackPtr - 2]['content']) {
            return;
        }

        if ($this->hasUse($phpcsFile, $tokens[$stackPtr]['content'])) {
            return;
        }

        $phpcsFile->addError(sprintf('Using the aliased class "%1$s" is deprecated. Use the original class "Contao\%1$s" instead.', $tokens[$stackPtr]['content']), $stackPtr, self::class);
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

            if (preg_match('/\\\\'.preg_quote($class, '/').'$/', $fqcn)) {
                return true;
            }
        }

        return false;
    }
}
