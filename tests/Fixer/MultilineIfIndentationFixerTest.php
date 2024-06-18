<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

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

    public static function getCodeSamples(): iterable
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
