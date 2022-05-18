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

use Contao\EasyCodingStandard\Fixer\ExpectsWithCallbackFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class ExpectsWithCallbackFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new ExpectsWithCallbackFixer();
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
                        $foo
                            ->method('bar')
                            ->with($this->callback(function ($a) { return $a; }));

                        $foo
                            ->method('bar')
                            ->with(
                                $this->callback(function ($b) {
                                    return $b;
                                })
                            );

                        $foo
                            ->method('bar')
                            ->with(
                                $this->callback(
                                    function ($c) {
                                        return $c;
                                    }
                                )
                            );
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
                        $foo
                            ->method('bar')
                            ->with($this->callback(function ($a) { return $a; }));

                        $foo
                            ->method('bar')
                            ->with($this->callback(function ($b) {
                                return $b;
                            }));

                        $foo
                            ->method('bar')
                            ->with($this->callback(
                                function ($c) {
                                    return $c;
                                }
                            ));
                    }
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo
                            ->method('bar')
                            ->with($bar, $this->callback(function ($a) { return $a; }));

                        $foo
                            ->method('bar')
                            ->with($bar, $this->callback(function ($b) {
                                return $b;
                            }));

                        $foo
                            ->method('bar')
                            ->with($bar, $this->callback(
                                function ($c) {
                                    return $c;
                                }
                            ));
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo
                            ->method('bar')
                            ->with(
                                $bar,
                                $this->callback(function ($a) { return $a; })
                            );

                        $foo
                            ->method('bar')
                            ->with(
                                $bar,
                                $this->callback(function ($b) {
                                    return $b;
                                })
                            );

                        $foo
                            ->method('bar')
                            ->with(
                                $bar,
                                $this->callback(
                                    function ($c) {
                                        return $c;
                                    }
                                )
                            );
                    }
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo
                            ->method('bar')
                            ->with($foo, $this->callback(function ($a) { return $a; }), $bar);

                        $foo
                            ->method('bar')
                            ->with($foo, $this->callback(function ($b) {
                                return $b;
                            }), $bar);

                        $foo
                            ->method('bar')
                            ->with($foo, $this->callback(
                                function ($c) {
                                    return $c;
                                }
                            ), $bar);
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo
                            ->method('bar')
                            ->with(
                                $foo,
                                $this->callback(function ($a) { return $a; }),
                                $bar
                            );

                        $foo
                            ->method('bar')
                            ->with(
                                $foo,
                                $this->callback(function ($b) {
                                    return $b;
                                }),
                                $bar
                            );

                        $foo
                            ->method('bar')
                            ->with(
                                $foo,
                                $this->callback(
                                    function ($c) {
                                        return $c;
                                    }
                                ),
                                $bar
                            );
                    }
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo
                            ->method('bar')
                            ->with($this->callback(function ($a) { return $a; }), $this->callback(function ($b) { return $b; }));

                        $foo
                            ->method('bar')
                            ->with($this->callback(function ($c) {
                                return $c;
                            }), $this->callback(function ($d) {
                                return $d;
                            }));

                        $foo
                            ->method('bar')
                            ->with($this->callback(
                                function ($e) {
                                    return $e;
                                }
                            ), $this->callback(
                                function ($f) {
                                    return $f;
                                }
                            ));
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                class FooTest
                {
                    public function testFoo(): void
                    {
                        $foo
                            ->method('bar')
                            ->with(
                                $this->callback(function ($a) { return $a; }),
                                $this->callback(function ($b) { return $b; })
                            );

                        $foo
                            ->method('bar')
                            ->with(
                                $this->callback(function ($c) {
                                    return $c;
                                }),
                                $this->callback(function ($d) {
                                    return $d;
                                })
                            );

                        $foo
                            ->method('bar')
                            ->with(
                                $this->callback(
                                    function ($e) {
                                        return $e;
                                    }
                                ),
                                $this->callback(
                                    function ($f) {
                                        return $f;
                                    }
                                )
                            );
                    }
                }
                EOT,
        ];
    }
}
