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

use Contao\EasyCodingStandard\Fixer\AssertEqualsFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class AssertEqualsFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new AssertEqualsFixer();
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

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
                        $foo = new Foo();

                        $this->assertEquals(42, $foo->getId());
                        $this->assertEquals('foo', $foo->getName());
                        $this->assertEquals(new Foo(), $foo);
                        $this->assertEquals([new Foo(), new Bar()], $foo->getObjects());
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo = new Foo();

                        $this->assertSame(42, $foo->getId());
                        $this->assertSame('foo', $foo->getName());
                        $this->assertEquals(new Foo(), $foo);
                        $this->assertEquals([new Foo(), new Bar()], $foo->getObjects());
                    }
                }
                EOT,
        ];
    }
}
