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

use Contao\EasyCodingStandard\Fixer\MultiLineLambdaFunctionArgumentsFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class MultiLineLambdaFunctionArgumentsFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new MultiLineLambdaFunctionArgumentsFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                $array = array_map(static function ($i) { return $i; }, $array);

                $array = array_map(static function ($i) {
                    return $i;
                }, $array);

                $array = array_map(
                    static function ($i) {
                        return $i;
                    },
                    $array
                );
                EOT,
            <<<'EOT'
                <?php

                $array = array_map(static function ($i) { return $i; }, $array);

                $array = array_map(
                    static function ($i) {
                        return $i;
                    },
                    $array
                );

                $array = array_map(
                    static function ($i) {
                        return $i;
                    },
                    $array
                );
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                $array = array_walk($array, static function ($i) { return $i; });

                $array = array_walk($array, static function ($i) {
                    return $i;
                });

                $array = array_walk(
                    $array,
                    static function ($i) {
                        return $i;
                    }
                );
                EOT,
            <<<'EOT'
                <?php

                $array = array_walk($array, static function ($i) { return $i; });

                $array = array_walk(
                    $array,
                    static function ($i) {
                        return $i;
                    }
                );

                $array = array_walk(
                    $array,
                    static function ($i) {
                        return $i;
                    }
                );
                EOT,
        ];
    }
}
