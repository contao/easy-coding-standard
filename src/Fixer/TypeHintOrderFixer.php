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
        return $tokens->isAnyTokenKindsFound([T_PUBLIC, T_PROTECTED, T_PRIVATE]);
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
            if (!$tokens[$index]->isGivenKind([T_PUBLIC, T_PROTECTED, T_PRIVATE])) {
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($index);

            // Ignore constants
            if ($tokens[$nextMeaningful]->isGivenKind(T_CONST)) {
                continue;
            }

            if ($tokens[$nextMeaningful]->isGivenKind(T_STATIC)) {
                $nextMeaningful = $tokens->getNextMeaningfulToken($nextMeaningful);
            }

            if (\defined('T_READONLY') && $tokens[$nextMeaningful]->isGivenKind(T_READONLY)) {
                $nextMeaningful = $tokens->getNextMeaningfulToken($nextMeaningful);
            }

            // No type hint
            if ($tokens[$nextMeaningful]->isGivenKind(T_VARIABLE)) {
                continue;
            }

            if ($tokens[$nextMeaningful]->isGivenKind(T_FUNCTION)) {
                $index = $this->handleFunction($tokens, $nextMeaningful);
            } else {
                $index = $this->handleClassProperty($tokens, $nextMeaningful);
            }
        }
    }

    private function handleFunction(Tokens $tokens, int $nextMeaningful): int
    {
        $end = $tokens->getNextTokenOfKind($nextMeaningful, [';', '{']);

        // Arguments
        $argsStart = $tokens->getNextTokenOfKind($nextMeaningful, ['(']);
        $argsEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $argsStart);
        $vars = $tokens->findGivenKind(T_VARIABLE, $argsStart, $argsEnd);

        if (\count($vars)) {
            foreach (array_keys($vars) as $pos) {
                $prevMeaningful = $tokens->getPrevMeaningfulToken($pos);

                // No type hint
                if ($tokens[$prevMeaningful]->equals('(') || $tokens[$prevMeaningful]->equals(',')) {
                    continue;
                }

                while ($prevMeaningful - 1 > $argsStart && !$tokens[$prevMeaningful - 1]->isGivenKind(T_WHITESPACE)) {
                    --$prevMeaningful;
                }

                if ($new = $this->orderTypeHint($tokens->generatePartialCode($prevMeaningful, $pos - 2))) {
                    $tokens->overrideRange($prevMeaningful, $pos - 2, $new);
                }
            }
        }

        // Return type
        $vars = $tokens->findGivenKind(CT::T_TYPE_COLON, $argsEnd, $end - 1);

        if (\count($vars)) {
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

    private function handleClassProperty(Tokens $tokens, int $nextMeaningful): int
    {
        $end = $tokens->getNextTokenOfKind($nextMeaningful, [';']);

        for ($i = $nextMeaningful; $i <= $end; ++$i) {
            if ($tokens[$i]->isGivenKind(T_VARIABLE)) {
                if ($new = $this->orderTypeHint($tokens->generatePartialCode($nextMeaningful, $i - 2))) {
                    $tokens->overrideRange($nextMeaningful, $i - 2, $new);
                }
                break;
            }
        }

        return $end;
    }

    private function orderTypeHint(string $typehint): ?Tokens
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

        return Tokens::fromCode($new);
    }
}
