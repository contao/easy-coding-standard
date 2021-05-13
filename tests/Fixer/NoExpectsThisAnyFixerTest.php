<?php

declare(strict_types=1);

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

    public function getCodeSamples(): \Generator
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
