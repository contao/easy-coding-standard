<?php

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class AsserEqualsFixer extends AbstractFixer
{
    /**
     * @var array
     */
    private $mapper = [
        'assertEquals' => 'assertSame',
        'assertNotEquals' => 'assertNotSame',
    ];

    public function getDefinition(): FixerDefinition
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
'
                ),
            ]
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

            if (!isset($this->mapper[$name]) || !$tokens[$index + 1]->equals('(')) {
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
