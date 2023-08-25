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
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class AssertEqualsFixer extends AbstractFixer
{
    private array $mapper = [
        'assertEquals' => 'assertSame',
        'assertNotEquals' => 'assertNotSame',
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Unless comparing objects, assertSame() should be used instead of asserEquals().',
            [
                new CodeSample(
                    '<?php

public function testFoo(): void
{
    $obj = new Foo();

    $this->assertSame("foo", $obj->getName());
    $this->assertEquals(new Foo(), $obj);
}
',
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_STRING)) {
                continue;
            }

            $name = $tokens[$index]->getContent();

            if (
                !isset($this->mapper[$name])
                || !$tokens[$index - 1]->isGivenKind(T_OBJECT_OPERATOR)
                || !$tokens[$index + 1]->equals('(')
            ) {
                continue;
            }

            $end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index + 1);

            $argumentsAnalyzer = new ArgumentsAnalyzer();
            $argumentsIndexes = $argumentsAnalyzer->getArguments($tokens, $index + 1, $end);

            $argStart = array_key_first($argumentsIndexes);
            $argEnd = $argumentsIndexes[$argStart];

            // Continue if the expected value is a or contains an object
            if ($tokens->findGivenKind(T_NEW, $argStart, $argEnd)) {
                continue;
            }

            $tokens->offsetSet($index, new Token([T_STRING, $this->mapper[$name]]));
        }
    }
}
