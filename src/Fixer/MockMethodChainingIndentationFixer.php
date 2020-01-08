<?php

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class MockMethodChainingIndentationFixer extends AbstractFixer
{
    /**
     * @var array
     */
    private static $methods = [
        'after',
        'expects',
        'method',
        'will',
        'willReturn',
        'willReturnReference',
        'willReturnMap',
        'willReturnArgument',
        'willReturnCallback',
        'willReturnSelf',
        'willReturnOnConsecutiveCalls',
        'willThrowException',
        'with',
        'withConsecutive',
        'withAnyParameters',
    ];

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Chained mock methods must be properly indented.',
            [
                new CodeSample(
                    '<?php

use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function testFoo(): void
    {
        $mock = $this->createMock(Foo::class);
        $mock
            ->method("isBar")
            ->willReturn(false);
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_OBJECT_OPERATOR);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_OBJECT_OPERATOR)) {
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($index);

            // Not a PHPUnit method
            if (!\in_array($tokens[$nextMeaningful]->getContent(), self::$methods, true)) {
                continue;
            }

            // Not a method call
            if (!$tokens[$nextMeaningful + 1]->equals('(')) {
                continue;
            }

            if ($this->isSingleMethodCall($tokens, $index)) {
                continue;
            }

            // The method call is indented already
            if ($tokens[$index - 1]->isGivenKind(T_WHITESPACE)) {
                continue;
            }

            $tokens->insertAt($index, new Token([T_WHITESPACE, "\n"]));
        }
    }

    private function isSingleMethodCall(Tokens $tokens, int $index): bool
    {
        $start = $tokens->getPrevTokenOfKind($index, [';', '{']);
        $end = $tokens->getNextTokenOfKind($index, [';']);

        return 1 === \count($tokens->findGivenKind(T_OBJECT_OPERATOR, $start, $end));
    }
}
