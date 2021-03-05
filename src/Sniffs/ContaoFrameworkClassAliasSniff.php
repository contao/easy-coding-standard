<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

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

        if ($this->hasUse($phpcsFile, 'Contao\\'.$tokens[$stackPtr]['content'])) {
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
        $end = $this->findNext($file, T_USE, 0);

        return (bool) $this->findNext($file, T_NAMESPACE, 0, $end);
    }

    private function hasUse(File $file, string $class): bool
    {
        $end = $this->findNext($file, [T_CLASS, T_INTERFACE, T_TRAIT], 0);
        $uses = $this->findNextAll($file, T_USE, 0, $end);

        foreach ($uses as $use) {
            $end = $this->findNext($file, T_SEMICOLON, $use + 2);
            $fqcn = $this->getContent($file, $use + 2, $end - 1);

            if ($fqcn === $class) {
                return true;
            }
        }

        return false;
    }

    private function findNext(File $phpcsFile, $types, int $startPointer, ?int $endPointer = null): ?int
    {
        $token = $phpcsFile->findNext($types, $startPointer, $endPointer, false);

        return false === $token ? null : $token;
    }

    private function findNextAll(File $phpcsFile, $types, int $startPointer, ?int $endPointer = null): array
    {
        $pointers = [];
        $actualStartPointer = $startPointer;

        while (true) {
            $pointer = $this->findNext($phpcsFile, $types, $actualStartPointer, $endPointer);

            if (null === $pointer) {
                break;
            }

            $pointers[] = $pointer;
            $actualStartPointer = $pointer + 1;
        }

        return $pointers;
    }

    private function getContent(File $phpcsFile, int $startPointer, ?int $endPointer = null): string
    {
        $tokens = $phpcsFile->getTokens();
        $endPointer = $endPointer ?? $this->getLastTokenPointer($phpcsFile);
        $content = '';

        for ($i = $startPointer; $i <= $endPointer; ++$i) {
            $content .= $tokens[$i]['content'];
        }

        return $content;
    }

    private function getLastTokenPointer(File $phpcsFile): int
    {
        $tokenCount = \count($phpcsFile->getTokens());

        if (0 === $tokenCount) {
            throw new \LogicException('Empty file: '.$phpcsFile->getFilename());
        }

        return $tokenCount - 1;
    }
}
