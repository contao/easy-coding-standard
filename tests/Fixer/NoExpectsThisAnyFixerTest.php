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

use Contao\EasyCodingStandard\Fixer\NoExpectsThisAnyFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class NoExpectsThisAnyFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new NoExpectsThisAnyFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        yield [
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo = $this->createMock(Foo::class);
                        $foo->expects($this->any())->method('bar');

                        $foo
                            ->expects($this->once())
                            ->method('bar');

                        $foo
                            ->expects($this->any())
                            ->method('bar')
                            ->willReturn(false);
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo = $this->createMock(Foo::class);
                        $foo->method('bar');

                        $foo
                            ->expects($this->once())
                            ->method('bar');

                        $foo
                            ->method('bar')
                            ->willReturn(false);
                    }
                }
                EOT,
        ];
    }
}
