<?php

declare(strict_types=1);

namespace Contao\EasyCodingStandard\Tests\Fixer;

use Contao\EasyCodingStandard\Fixer\MultiLineIfIndentationFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class MultilineIfIndentationFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new MultiLineIfIndentationFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                if (isset($array["bar"]) && is_array($array["bar"])) {
                }

                if (isset($array["bar"])
                    && is_array($array["bar"])) {
                }

                if (isset($array["bar"])
                    && is_array($array["bar"])
                ) {
                }

                if (
                    isset($array["bar"])
                    && is_array($array["bar"])
                ) {
                }
                EOT,
            <<<'EOT'
                <?php

                if (isset($array["bar"]) && is_array($array["bar"])) {
                }

                if (
                    isset($array["bar"])
                    && is_array($array["bar"])
                ) {
                }

                if (
                    isset($array["bar"])
                    && is_array($array["bar"])
                ) {
                }

                if (
                    isset($array["bar"])
                    && is_array($array["bar"])
                ) {
                }
                EOT,
        ];
    }
}
