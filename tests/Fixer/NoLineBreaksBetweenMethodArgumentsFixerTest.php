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

use Contao\EasyCodingStandard\Fixer\NoLineBreakBetweenMethodArgumentsFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class NoLineBreaksBetweenMethodArgumentsFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new NoLineBreakBetweenMethodArgumentsFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());

        $tokens = Tokens::fromCode($tokens->generateCode());
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        foreach (['line' => '// Parameter', 'hash' => '# Parameter', 'block' => '/* Parameter */', 'doc' => '/** Parameter */'] as $name => $comment) {
            yield $name.' comments in parameter lists are kept' => [
                str_replace(
                    'COMMENT',
                    $comment,
                    <<<'EOT'
                        <?php

                        class Foo
                        {
                            public function foo(
                                COMMENT
                                string $foo, COMMENT
                            ): void
                            {
                            }

                            public function bar(
                                string $bar,
                                string $baz,
                            ): void
                            {
                            }
                        }
                        EOT,
                ),
                str_replace(
                    'COMMENT',
                    $comment,
                    <<<'EOT'
                        <?php

                        class Foo
                        {
                            public function foo(
                                COMMENT
                                string $foo, COMMENT
                            ): void
                            {
                            }

                            public function bar(string $bar, string $baz,): void
                            {
                            }
                        }
                        EOT,
                ),
            ];
        }

        yield [
            <<<'EOT'
                <?php

                class Foo
                {
                    public function __construct(
                        FooService $fooService,
                        BarService $barService
                    ) {
                    }

                    public function bar(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): Generator
                    {
                        return function (
                            string $key,
                            $value
                        ) {
                            return $key.' '.$value;
                        };
                    }
                }

                class Bar
                {
                    public function __construct(
                        private readonly FooService $fooService,
                        private readonly BarService $barService
                    ) {
                    }

                    public function foo(
                        FooService $fooService,
                        BarService $barService,
                        array $options = [],
                        Logger $logger = null
                    ): Generator {
                        return function (string $key, $value) { return $key.' '.$value; };
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                class Foo
                {
                    public function __construct(FooService $fooService, BarService $barService)
                    {
                    }

                    public function bar(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): Generator
                    {
                        return function (string $key, $value) {
                            return $key.' '.$value;
                        };
                    }
                }

                class Bar
                {
                    public function __construct(
                        private readonly FooService $fooService,
                        private readonly BarService $barService
                    ) {
                    }

                    public function foo(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): Generator
                    {
                        return function (string $key, $value) { return $key.' '.$value; };
                    }
                }
                EOT,
        ];
    }
}
