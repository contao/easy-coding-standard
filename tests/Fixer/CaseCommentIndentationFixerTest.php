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

use Contao\EasyCodingStandard\Fixer\CaseCommentIndentationFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class CaseCommentIndentationFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new CaseCommentIndentationFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        yield [
            <<<'EOT'
                <?php

                $a = false;
                $b = false;

                switch (true) {
                        // First case
                    case 1:
                        // do nothing
                        break;

                        // Second case
                        // Multi-line comment
                    case 2:
                        $a = true;
                        // no break

                    case 3:
                        $b = true;
                        break;

                    // Default case
                    default:
                        $a = true;
                        $b = true;
                }
                EOT,
            <<<'EOT'
                <?php

                $a = false;
                $b = false;

                switch (true) {
                    // First case
                    case 1:
                        // do nothing
                        break;

                    // Second case
                    // Multi-line comment
                    case 2:
                        $a = true;
                        // no break

                    case 3:
                        $b = true;
                        break;

                    // Default case
                    default:
                        $a = true;
                        $b = true;
                }
                EOT,
        ];
    }
}
