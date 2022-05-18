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

use Contao\EasyCodingStandard\Fixer\IsArrayNotEmptyFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class IsArrayNotEmptyFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new IsArrayNotEmptyFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                $array = [];

                if (is_array($array['foo']) && isset($array['foo'])) {
                }

                if (is_array($array['foo']) && !empty($array['foo'])) {
                }

                if (\is_array($array['foo']) && !empty($array['foo'])) {
                }
                EOT,
            <<<'EOT'
                <?php

                $array = [];

                if (isset($array['foo']) && is_array($array['foo'])) {
                }

                if (!empty($array['foo']) && is_array($array['foo'])) {
                }

                if (!empty($array['foo']) && \is_array($array['foo'])) {
                }
                EOT,
        ];
    }
}
