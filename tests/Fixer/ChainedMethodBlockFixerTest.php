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

use Contao\EasyCodingStandard\Fixer\ChainedMethodBlockFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class ChainedMethodBlockFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new ChainedMethodBlockFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        yield [
            <<<'EOT'
                <?php

                use PHPUnit\Framework\TestCase;

                class SomeTest extends TestCase
                {
                    public function testFoo(): void
                    {
                        $mock = $this->createMock(Foo::class);
                        $mock
                            ->method("isFoo")
                            ->willReturn(true)
                        ;
                        $mock
                            ->method("isBar")
                            ->willReturn(false)
                        ;

                        $mock->isFoo();

                        if (true) {
                            $mock
                                ->method("isBaz")
                                ->willReturn(false)
                            ;
                        }

                        $mock
                            ->method("isBat")
                            ->willReturnCallback(
                                function () {
                                    $bar = $this->createMock(Bar::class);
                                    $bar
                                        ->method("isFoo")
                                        ->willReturn(false)
                                    ;
                                    $bar
                                        ->method("isBar")
                                        ->willReturn(true)
                                    ;
                                }
                            )
                        ;
                    }

                    public function testBar(): void
                    {
                        /*
                         * Comment
                         */

                        $this->mock = $this->createMock(Bar::class);

                        $this->mock
                            ->method("isFoo")
                            ->willReturn(false)
                        ;

                        $this->mock
                            ->method("isBar")
                            ->willReturn(true)
                        ;
                    }

                    public function testBaz(): void
                    {
                        $mock = $this->mockClassWithProperties(Baz::class);
                        $mock->id = 42;
                        $mock
                            ->method("isFoo")
                            ->willReturn(false)
                        ;
                        $mock
                            ->method("isBaz")
                            ->willReturn(true)
                        ;
                    }

                    public function testQux(): void
                    {
                        $mock = $this->mockClassWithProperties(Qux::class, [
                            'id' => 42,
                        ]);
                        $mock
                            ->method("isQux")
                            ->willReturn(true)
                        ;
                    }

                    public function testQuux(): void
                    {
                        /** @phpstan-ignore class.notFound */
                        $this->mock = $this->createMock(Quux::class);
                        /** @phpstan-ignore class.notFound */
                        $this->mock
                            ->method("isQuux")
                            ->willReturn(false)
                        ;
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                use PHPUnit\Framework\TestCase;

                class SomeTest extends TestCase
                {
                    public function testFoo(): void
                    {
                        $mock = $this->createMock(Foo::class);
                        $mock
                            ->method("isFoo")
                            ->willReturn(true)
                        ;

                        $mock
                            ->method("isBar")
                            ->willReturn(false)
                        ;

                        $mock->isFoo();

                        if (true) {
                            $mock
                                ->method("isBaz")
                                ->willReturn(false)
                            ;
                        }

                        $mock
                            ->method("isBat")
                            ->willReturnCallback(
                                function () {
                                    $bar = $this->createMock(Bar::class);
                                    $bar
                                        ->method("isFoo")
                                        ->willReturn(false)
                                    ;

                                    $bar
                                        ->method("isBar")
                                        ->willReturn(true)
                                    ;
                                }
                            )
                        ;
                    }

                    public function testBar(): void
                    {
                        /*
                         * Comment
                         */

                        $this->mock = $this->createMock(Bar::class);
                        $this->mock
                            ->method("isFoo")
                            ->willReturn(false)
                        ;

                        $this->mock
                            ->method("isBar")
                            ->willReturn(true)
                        ;
                    }

                    public function testBaz(): void
                    {
                        $mock = $this->mockClassWithProperties(Baz::class);
                        $mock->id = 42;

                        $mock
                            ->method("isFoo")
                            ->willReturn(false)
                        ;

                        $mock
                            ->method("isBaz")
                            ->willReturn(true)
                        ;
                    }

                    public function testQux(): void
                    {
                        $mock = $this->mockClassWithProperties(Qux::class, [
                            'id' => 42,
                        ]);

                        $mock
                            ->method("isQux")
                            ->willReturn(true)
                        ;
                    }

                    public function testQuux(): void
                    {
                        /** @phpstan-ignore class.notFound */
                        $this->mock = $this->createMock(Quux::class);

                        /** @phpstan-ignore class.notFound */
                        $this->mock
                            ->method("isQuux")
                            ->willReturn(false)
                        ;
                    }
                }
                EOT,
        ];
    }
}
