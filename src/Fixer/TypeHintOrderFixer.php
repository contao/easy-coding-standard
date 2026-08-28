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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

final class TypeHintOrderFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    private const TYPE_CHECK_TEMPLATE = '<?php function f(): %s {}';

    private static array $nativeTypes = [
        'array',
        'callable',
        'bool',
        'float',
        'int',
        'string',
        'iterable',
        'object',
        'mixed',
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Type hints must be ordered and grouped by non-native types and native types.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php

                        class Foo
                        {
                            public function __construct(
                                private readonly FooService|null $fooService,
                                private int|iterable $count,
                                private readonly Logger|null $logger = null
                            ) {
                            }
                        }
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_PUBLIC, T_PROTECTED, T_PRIVATE, T_FUNCTION, T_FN]);
    }

    /**
     * Must run after NoUselessReturnFixer.
     */
    public function getPriority(): int
    {
        return -20;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind([T_PUBLIC, T_PROTECTED, T_PRIVATE, T_FUNCTION, T_FN])) {
                continue;
            }

            // Anonymous functions
            if ($tokens[$index]->isGivenKind([T_FUNCTION, T_FN])) {
                $index = $this->handleFunction($tokens, $index);
                continue;
            }

            $next = $tokens->getNextMeaningfulToken($index);

            // Ignore constants
            if ($tokens[$next]->isGivenKind(T_CONST)) {
                continue;
            }

            if ($tokens[$next]->isGivenKind(T_STATIC)) {
                $next = $tokens->getNextMeaningfulToken($next);
            }

            if (\defined('T_READONLY') && $tokens[$next]->isGivenKind(T_READONLY)) {
                $next = $tokens->getNextMeaningfulToken($next);
            }

            // No type hint
            if ($tokens[$next]->isGivenKind(T_VARIABLE)) {
                continue;
            }

            if ($tokens[$next]->isGivenKind(T_FUNCTION)) {
                $index = $this->handleFunction($tokens, $next);
            } else {
                $index = $this->handleClassProperty($tokens, $next);
            }
        }
    }

    private function handleFunction(Tokens $tokens, int $next): int
    {
        $end = $tokens->getNextTokenOfKind($next, [';', '{']);

        // Arguments
        $argsStart = $tokens->getNextTokenOfKind($next, ['(']);
        $argsEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $argsStart);
        $vars = $tokens->findGivenKind(T_VARIABLE, $argsStart, $argsEnd);

        if ([] !== $vars) {
            foreach (array_keys($vars) as $pos) {
                $prev = $tokens->getPrevMeaningfulToken($pos);

                // No type hint
                if ($tokens[$prev]->equals('(') || $tokens[$prev]->equals(',')) {
                    continue;
                }

                while ($prev - 1 > $argsStart && !$tokens[$prev - 1]->isGivenKind(T_WHITESPACE)) {
                    --$prev;
                }

                if ($new = $this->orderTypeHint($tokens->generatePartialCode($prev, $pos - 2))) {
                    $tokens->overrideRange($prev, $pos - 2, $new);
                }
            }
        }

        // Return type
        $vars = $tokens->findGivenKind(CT::T_TYPE_COLON, $argsEnd, $end - 1);

        if ([] !== $vars) {
            $start = $stop = array_key_first($vars) + 2;

            while ($stop < $end - 1 && !$tokens[$stop + 1]->isGivenKind(T_WHITESPACE)) {
                ++$stop;
            }

            if ($new = $this->orderTypeHint($tokens->generatePartialCode($start, $stop))) {
                $tokens->overrideRange($start, $stop, $new);
            }
        }

        return $end;
    }

    private function handleClassProperty(Tokens $tokens, int $next): int
    {
        $end = $tokens->getNextTokenOfKind($next, [';']);

        for ($i = $next; $i <= $end; ++$i) {
            if ($tokens[$i]->isGivenKind(T_VARIABLE)) {
                if ($new = $this->orderTypeHint($tokens->generatePartialCode($next, $i - 2))) {
                    $tokens->overrideRange($next, $i - 2, $new);
                }
                break;
            }
        }

        return $end;
    }

    private function orderTypeHint(string $typehint): Tokens|null
    {
        if (!str_contains($typehint, '|') && !str_contains($typehint, '?')) {
            return null;
        }

        $natives = [];
        $objects = [];
        $hasFalse = false;
        $hasNull = false;

        $chunks = explode('|', $typehint);

        foreach ($chunks as $chunk) {
            if ('?' === $chunk[0]) {
                $chunk = substr($chunk, 1);
                $hasNull = true;
            }

            if ('false' === $chunk) {
                $hasFalse = true;
            } elseif ('null' === $chunk) {
                $hasNull = true;
            } elseif (\in_array($chunk, self::$nativeTypes, true)) {
                $natives[$chunk] = $chunk;
            } else {
                $objects[ltrim($chunk, '\\')] = $chunk;
            }
        }

        ksort($natives);
        ksort($objects);

        $new = implode('|', [...array_values($objects), ...array_values($natives)]);

        if ($hasFalse) {
            $new .= '|false';
        }

        if ($hasNull) {
            $new .= '|null';
        }

        return $this->tokenizeTypeHint($new);
    }

    private function tokenizeTypeHint(string $typehint): Tokens
    {
        $tokens = Tokens::fromCode(\sprintf(self::TYPE_CHECK_TEMPLATE, $typehint));
        $typeColon = array_key_first($tokens->findGivenKind(CT::T_TYPE_COLON));
        $openingBrace = $tokens->getNextTokenOfKind($typeColon, ['{']);
        $typeStart = $tokens->getNextMeaningfulToken($typeColon);
        $typeEnd = $tokens->getPrevMeaningfulToken($openingBrace);

        $tokens->overrideRange(0, $typeStart - 1, []);
        $tokens->overrideRange($typeEnd + 1, \count($tokens) - 1, []);
        $tokens->clearEmptyTokens();

        return $tokens;
    }
}
