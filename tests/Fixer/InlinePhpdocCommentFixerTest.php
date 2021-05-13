<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Tests\Fixer;

use Contao\EasyCodingStandard\Fixer\InlinePhpdocCommentFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class InlinePhpdocCommentFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new InlinePhpdocCommentFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                /* This is just an inline comment */

                /* @var string $foo */

                /* Not a phpDoc @var string $foo */

                /*
                 * Multi-line comments should be ignored, too
                 */
                EOT,
            <<<'EOT'
                <?php

                /* This is just an inline comment */

                /** @var string $foo */

                /* Not a phpDoc @var string $foo */

                /*
                 * Multi-line comments should be ignored, too
                 */
                EOT,
        ];
    }
}
