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

use Contao\EasyCodingStandard\Fixer\MockMethodChainingIndentationFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class MockMethodChainingIndentationFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new MockMethodChainingIndentationFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        yield 'a chain at the beginning of the file is indented' => [
            <<<'EOT'
                <?php

                $mock->method('foo')->willReturn(false);
                EOT,
            <<<'EOT'
                <?php

                $mock
                    ->method('foo')
                    ->willReturn(false);
                EOT,
        ];

        $code = <<<'EOT'
            <?php

            $mock->method('foo')->willReturn(false) ?>
            EOT;

        yield 'a chain without a terminating semicolon is preserved' => [$code, $code];

        yield [
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo = $this->createMock(Foo::class);
                        $foo->method('bar');

                        $foo->method('bar')->willReturn(false);

                        $foo
                            ->expects($this->once())
                            ->method('bar')
                            ->willReturn(false);

                        $foo->method('bar')->with('foo')->willReturnSelf();

                        $this->foo->method('bar');

                        $this->foo->method('bar')->with('foo')->willReturnSelf();
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
                            ->method('bar')
                            ->willReturn(false);

                        $foo
                            ->expects($this->once())
                            ->method('bar')
                            ->willReturn(false);

                        $foo
                            ->method('bar')
                            ->with('foo')
                            ->willReturnSelf();

                        $this->foo->method('bar');

                        $this->foo
                            ->method('bar')
                            ->with('foo')
                            ->willReturnSelf();
                    }
                }
                EOT,
        ];
    }
}
