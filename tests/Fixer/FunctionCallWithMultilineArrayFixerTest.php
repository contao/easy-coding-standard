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

use Contao\EasyCodingStandard\Fixer\FunctionCallWithMultilineArrayFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class FunctionCallWithMultilineArrayFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new FunctionCallWithMultilineArrayFixer();
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                functionFoo([
                    'foo' => 'bar',
                ]);
                EOT,
            <<<'EOT'
                <?php

                functionFoo([
                    'foo' => 'bar',
                ]);
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo($foo, [
                    'foo' => 'bar',
                ]);
                EOT,
            <<<'EOT'
                <?php

                functionFoo($foo, [
                    'foo' => 'bar',
                ]);
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo(
                    $foo,
                    [
                        'foo' => 'bar',
                    ],
                );
                EOT,
            <<<'EOT'
                <?php

                functionFoo($foo, [
                    'foo' => 'bar',
                ]);
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo($foo, [
                    'foo' => 'bar',
                ], true);
                EOT,
            <<<'EOT'
                <?php

                functionFoo(
                    $foo,
                    [
                        'foo' => 'bar',
                    ],
                    true,
                );
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo([
                    'foo' => 'bar',
                ], true);
                EOT,
            <<<'EOT'
                <?php

                functionFoo(
                    [
                        'foo' => 'bar',
                    ],
                    true,
                );
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo([
                        'foo' => 'bar',
                    ]);
                }
                EOT,
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo([
                        'foo' => 'bar',
                    ]);
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo($foo, [
                        'foo' => 'bar',
                    ]);
                }
                EOT,
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo($foo, [
                        'foo' => 'bar',
                    ]);
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo(
                        $foo,
                        [
                            'foo' => 'bar',
                        ],
                    );
                }
                EOT,
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo($foo, [
                        'foo' => 'bar',
                    ]);
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo($foo, [
                        'foo' => 'bar',
                    ], true);
                }
                EOT,
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo(
                        $foo,
                        [
                            'foo' => 'bar',
                        ],
                        true,
                    );
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo([
                        'foo' => 'bar',
                    ], true);
                }
                EOT,
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo(
                        [
                            'foo' => 'bar',
                        ],
                        true,
                    );
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo([[
                    'foo' => 'bar',
                ]]);
                EOT,
            <<<'EOT'
                <?php

                functionFoo([[
                    'foo' => 'bar',
                ]]);
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo($foo, ...[
                    'foo' => 'bar',
                    'bar' => 'baz',
                ]);
                EOT,
            <<<'EOT'
                <?php

                functionFoo($foo, ...[
                    'foo' => 'bar',
                    'bar' => 'baz',
                ]);
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo(
                    $foo,
                    ...[
                        'foo' => 'bar',
                        'bar' => 'baz',
                    ],
                );
                EOT,
            <<<'EOT'
                <?php

                functionFoo($foo, ...[
                    'foo' => 'bar',
                    'bar' => 'baz',
                ]);
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo($foo, ...[
                    'foo' => 'bar',
                    'bar' => 'baz',
                ], true);
                EOT,
            <<<'EOT'
                <?php

                functionFoo(
                    $foo,
                    ...[
                        'foo' => 'bar',
                        'bar' => 'baz',
                    ],
                    true,
                );
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo(...[
                    'foo' => 'bar',
                    'bar' => 'baz',
                ], true);
                EOT,
            <<<'EOT'
                <?php

                functionFoo(
                    ...[
                        'foo' => 'bar',
                        'bar' => 'baz',
                    ],
                    true,
                );
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo($foo, ['foo' => 'bar']);
                }
                EOT,
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo($foo, ['foo' => 'bar']);
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo(
                    $foo,
                    [
                        'foo' => 'bar',
                    ],
                );
                EOT,
            <<<'EOT'
                <?php

                functionFoo($foo, [
                    'foo' => 'bar',
                ]);
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo(
                    $foo,
                    ['foo' => 'bar'],
                );
                EOT,
            <<<'EOT'
                <?php

                functionFoo(
                    $foo,
                    ['foo' => 'bar'],
                );
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo(
                    [
                        'foo' => 'bar',
                    ],
                    [
                        'bar' => 'baz',
                    ],
                );
                EOT,
            <<<'EOT'
                <?php

                functionFoo(
                    [
                        'foo' => 'bar',
                    ],
                    [
                        'bar' => 'baz',
                    ],
                );
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                functionFoo(
                    new Foo(),
                    [
                        'foo' => 'bar',
                    ],
                );
                EOT,
            <<<'EOT'
                <?php

                functionFoo(
                    new Foo(),
                    [
                        'foo' => 'bar',
                    ],
                );
                EOT,
        ];
    }
}
