<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;

final class UseSprintfInExceptionsSniff implements Sniff
{
    public function register(): array
    {
        return [T_THROW];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (T_THROW !== $tokens[$stackPtr]['code']) {
            return;
        }

        $next = $this->getNextNonWhitespaceToken($tokens, $stackPtr);

        // We are not dealing with "throw new"
        if (T_NEW !== $tokens[$next]['code']) {
            return;
        }

        $next = TokenHelper::findNext($phpcsFile, T_STRING, $next);

        // We are not dealing with an exception class
        if ('Exception' !== substr($tokens[$next]['content'], -9)) {
            return;
        }

        $next = $this->getNextNonWhitespaceToken($tokens, $next);

        // There is no opening parenthesis after the class name
        if (T_OPEN_PARENTHESIS !== $tokens[$next]['code']) {
            return;
        }

        $next = $this->getNextNonWhitespaceToken($tokens, $next);

        // A non-interpolated string will have the T_CONSTANT_ENCAPSED_STRING
        // code, so it is enough to check for T_DOUBLE_QUOTED_STRING here
        if (T_DOUBLE_QUOTED_STRING !== $tokens[$next]['code']) {
            return;
        }

        $phpcsFile->addError('Using string interpolation in exception messages is not allowed. Use sprintf() instead.', $stackPtr, self::class);
    }

    private function getNextNonWhitespaceToken(array $tokens, int $index): int
    {
        do {
            ++$index;
        } while (T_WHITESPACE === $tokens[$index]['code']);

        return $index;
    }
}
