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

    public static function getCodeSamples(): iterable
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
