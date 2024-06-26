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

use Contao\EasyCodingStandard\Fixer\NoSemicolonAfterShortEchoTagFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class NoSemicolonAfterShortEchoTagFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new NoSemicolonAfterShortEchoTagFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        yield [
            <<<'EOT'
                <?= $this->a; ?>
                <?= $this->b ?>
                <?php echo $c; ?>
                <?php echo $d ?>

                <?php $foo = 'bar'; ?>

                <?php
                    if ($e) {
                        echo $e;
                    }
                ?>

                <?php
                    someFunction($f);
                    echo $g;
                ?>

                <?= $this->z; ?>

                <?php
                    echo $h;
                    echo $i;
                EOT,
            <<<'EOT'
                <?= $this->a ?>
                <?= $this->b ?>
                <?php echo $c; ?>
                <?php echo $d ?>

                <?php $foo = 'bar'; ?>

                <?php
                    if ($e) {
                        echo $e;
                    }
                ?>

                <?php
                    someFunction($f);
                    echo $g;
                ?>

                <?= $this->z ?>

                <?php
                    echo $h;
                    echo $i;
                EOT,
        ];
    }
}
