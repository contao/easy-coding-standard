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

use Contao\EasyCodingStandard\Fixer\MultilineCallArgumentsFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class MultilineCallArgumentsFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new MultilineCallArgumentsFixer();
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

        $this->assertSame($expected, $tokens->generateCode());

        // The fixer must be idempotent
        $tokens = Tokens::fromCode($expected);
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function testPreservesTheValueOfATabIndentedHeredoc(): void
    {
        $code = "<?php\nreturn sprintf(<<<'EOD'\n\t%s\n\t  bar\n\tEOD, 'baz');";
        $expected = "<?php\nreturn sprintf(\n    <<<'EOD'\n     %s\n       bar\n     EOD,\n    'baz',\n);";
        $tokens = Tokens::fromCode($code);

        $fixer = new MultilineCallArgumentsFixer();
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

        $this->assertSame($expected, $tokens->generateCode());

        $this->assertSame(eval(substr($code, 5)), eval(substr($tokens->generateCode(), 5)));

        $tokens = Tokens::fromCode($expected);
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        yield 'literal callable names with literal arguments are candidates' => [
            <<<'EOT'
                <?php

                ('sprintf')('%s',
                    'foo');
                EOT,
            <<<'EOT'
                <?php

                ('sprintf')(
                    '%s',
                    'foo',
                );
                EOT,
        ];

        $unchanged = <<<'EOT'
            <?php

            if ($condition) (<<<'EOD'
            foo
            EOD);
            EOT;

        yield 'parenthesized expressions after control conditions are not calls' => [$unchanged, $unchanged];

        yield 'anonymous constructors with only literal arguments are candidates' => [
            <<<'EOT'
                <?php

                new class(1,
                    2) {};
                EOT,
            <<<'EOT'
                <?php

                new class(
                    1,
                    2,
                ) {};
                EOT,
        ];

        yield 'parenthesized callables have one argument per line' => [
            <<<'EOT'
                <?php

                ($callback)($a,
                    $b);
                EOT,
            <<<'EOT'
                <?php

                ($callback)(
                    $a,
                    $b,
                );
                EOT,
        ];

        yield 'immediately invoked closures have one argument per line' => [
            <<<'EOT'
                <?php

                (static function ($a, $b) {})($a,
                    $b);
                EOT,
            <<<'EOT'
                <?php

                (static function ($a, $b) {})(
                    $a,
                    $b,
                );
                EOT,
        ];

        yield 'dynamic method calls have one argument per line' => [
            <<<'EOT'
                <?php

                $object->{$method}($a,
                    $b);
                EOT,
            <<<'EOT'
                <?php

                $object->{$method}(
                    $a,
                    $b,
                );
                EOT,
        ];

        yield 'calls to callable array elements have one argument per line' => [
            <<<'EOT'
                <?php

                $callbacks[0]($a,
                    $b);
                EOT,
            <<<'EOT'
                <?php

                $callbacks[0](
                    $a,
                    $b,
                );
                EOT,
        ];

        yield 'anonymous class constructors have one argument per line' => [
            <<<'EOT'
                <?php

                $foo = new class($a,
                    $b) {};
                EOT,
            <<<'EOT'
                <?php

                $foo = new class(
                    $a,
                    $b,
                ) {};
                EOT,
        ];

        yield 'multi-line array as only argument' => [
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

        yield 'multi-line array as second of two arguments' => [
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

        yield 'removes the line breaks if the array is the second of two arguments' => [
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

        yield 'multi-line array followed by another argument' => [
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
                    ['foo' => 'bar'],
                    true,
                );
                EOT,
        ];

        yield 'multi-line array as first of two arguments' => [
            <<<'EOT'
                <?php

                functionFoo([
                    'foo' => 'bar',
                ], true);
                EOT,
            <<<'EOT'
                <?php

                functionFoo(
                    ['foo' => 'bar'],
                    true,
                );
                EOT,
        ];

        yield 'indented code' => [
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo([
                        'foo' => 'bar',
                    ]);

                    functionFoo($foo, [
                        'foo' => 'bar',
                    ]);

                    functionFoo(
                        $foo,
                        [
                            'foo' => 'bar',
                        ],
                    );

                    functionFoo($foo, [
                        'foo' => 'bar',
                    ], true);

                    functionFoo([
                        'foo' => 'bar',
                    ], true);
                }
                EOT,
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo([
                        'foo' => 'bar',
                    ]);

                    functionFoo($foo, [
                        'foo' => 'bar',
                    ]);

                    functionFoo($foo, [
                        'foo' => 'bar',
                    ]);

                    functionFoo(
                        $foo,
                        ['foo' => 'bar'],
                        true,
                    );

                    functionFoo(
                        ['foo' => 'bar'],
                        true,
                    );
                }
                EOT,
        ];

        yield 'nested multi-line array is expanded' => [
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

        yield 'spread array as second of two arguments' => [
            <<<'EOT'
                <?php

                functionFoo($foo, ...[
                    'foo' => 'bar',
                    'bar' => 'baz',
                ]);

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

                functionFoo($foo, ...[
                    'foo' => 'bar',
                    'bar' => 'baz',
                ]);
                EOT,
        ];

        yield 'spread array followed by another argument' => [
            <<<'EOT'
                <?php

                functionFoo($foo, ...[
                    'foo' => 'bar',
                    'bar' => 'baz',
                ], true);

                functionFoo(...[
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

                functionFoo(
                    ...[
                        'foo' => 'bar',
                        'bar' => 'baz',
                    ],
                    true,
                );
                EOT,
        ];

        yield 'single-line calls, arrays and long statements are not changed' => [
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo($foo, ['foo' => 'bar']);
                }

                $foo = ['foo' => ['bar', 'baz']];

                functionFoo(
                    $foo,
                    ['foo' => 'bar'],
                );

                class Foo
                {
                    public function bar(): void
                    {
                        $this->assertSame('a long string that makes the line too long', $this->getSomething($foo, $bar), 'a longer message');
                        $foo = sprintf('%s', $this->getSomething('a very long argument', 'another very long argument', 'and a third long argument'));
                        $foo = ['a very long element', 'another very long element', 'and a third very long element', 'and one more long element'];

                        return $tokens[$index - 1]->isGivenKind([T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM]) && $tokens[$index + 1]->equals('(');
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                if (true) {
                    functionFoo($foo, ['foo' => 'bar']);
                }

                $foo = ['foo' => ['bar', 'baz']];

                functionFoo(
                    $foo,
                    ['foo' => 'bar'],
                );

                class Foo
                {
                    public function bar(): void
                    {
                        $this->assertSame('a long string that makes the line too long', $this->getSomething($foo, $bar), 'a longer message');
                        $foo = sprintf('%s', $this->getSomething('a very long argument', 'another very long argument', 'and a third long argument'));
                        $foo = ['a very long element', 'another very long element', 'and a third very long element', 'and one more long element'];

                        return $tokens[$index - 1]->isGivenKind([T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM]) && $tokens[$index + 1]->equals('(');
                    }
                }
                EOT,
        ];

        yield 'already expanded calls are compacted' => [
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
                    ['foo' => 'bar'],
                    ['bar' => 'baz'],
                );

                functionFoo(
                    new Foo(),
                    ['foo' => 'bar'],
                );
                EOT,
        ];

        yield 'method calls, static calls and new' => [
            <<<'EOT'
                <?php

                $this->foo($bar, [
                    'foo' => 'bar',
                ], $baz);

                $foo?->bar($bar, $this->baz(
                    'baz',
                ));

                Foo::bar($bar, new Baz([
                    'foo' => 'bar',
                ]));

                $foo = new Foo($bar, function () {
                    return 'baz';
                });

                $foo = new static($bar, function () {
                    return 'baz';
                });
                EOT,
            <<<'EOT'
                <?php

                $this->foo(
                    $bar,
                    ['foo' => 'bar'],
                    $baz,
                );

                $foo?->bar(
                    $bar,
                    $this->baz(
                        'baz',
                    ),
                );

                Foo::bar(
                    $bar,
                    new Baz([
                        'foo' => 'bar',
                    ]),
                );

                $foo = new Foo(
                    $bar,
                    function () {
                        return 'baz';
                    },
                );

                $foo = new static(
                    $bar,
                    function () {
                        return 'baz';
                    },
                );
                EOT,
        ];

        yield 'multi-line nested call' => [
            <<<'EOT'
                <?php

                $factory = new DataContainerSchemaFactory($this->createContaoFrameworkStub([
                    Controller::class => $controller,
                    System::class => $this->createAdapterStub(['loadLanguageFile']),
                ]), $this->converters, $this->relationResolver, $this->localeSwitcher, $this->createRelationDefinitionFactory($this->converters));
                EOT,
            <<<'EOT'
                <?php

                $factory = new DataContainerSchemaFactory(
                    $this->createContaoFrameworkStub([
                        Controller::class => $controller,
                        System::class => $this->createAdapterStub(['loadLanguageFile']),
                    ]),
                    $this->converters,
                    $this->relationResolver,
                    $this->localeSwitcher,
                    $this->createRelationDefinitionFactory($this->converters),
                );
                EOT,
        ];

        yield 'nested array argument is expanded' => [
            <<<'EOT'
                <?php

                $record = new DataContainerRecord('tl_content', ['rows' => [
                    (object) ['file' => new RelationReference('file-id', '/files/file-id')],
                    new \stdClass(),
                ]], 17);
                EOT,
            <<<'EOT'
                <?php

                $record = new DataContainerRecord(
                    'tl_content',
                    [
                        'rows' => [
                            (object) ['file' => new RelationReference('file-id', '/files/file-id')],
                            new \stdClass(),
                        ],
                    ],
                    17,
                );
                EOT,
        ];

        yield 'arrays' => [
            <<<'EOT'
                <?php

                $foo = ['foo' => [
                    'bar' => 'Bar',
                    'baz' => 'Baz',
                ]];

                $foo = [
                    'a', 'b',
                ];

                $foo = ['foo' => $this->bar(
                    'bar',
                )];

                $foo = [[[
                    'foo',
                ]]];
                EOT,
            <<<'EOT'
                <?php

                $foo = [
                    'foo' => [
                        'bar' => 'Bar',
                        'baz' => 'Baz',
                    ],
                ];

                $foo = [
                    'a',
                    'b',
                ];

                $foo = [
                    'foo' => $this->bar(
                        'bar',
                    ),
                ];

                $foo = [[[
                    'foo',
                ]]];
                EOT,
        ];

        yield 'destructuring and array access are not changed' => [
            <<<'EOT'
                <?php

                [$a, $b] = $foo;
                [$aaaaaaaaaaaaaaaaaaaaaaaaaa, $bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb, $cccccccccccccccccccccccccccccccccccccccccc, $dddddddddddd] = $foo;
                $foo = $bar['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']['bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb']['cccccccccccccccccccccccccccccccccc'];
                EOT,
            <<<'EOT'
                <?php

                [$a, $b] = $foo;
                [$aaaaaaaaaaaaaaaaaaaaaaaaaa, $bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb, $cccccccccccccccccccccccccccccccccccccccccc, $dddddddddddd] = $foo;
                $foo = $bar['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']['bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb']['cccccccccccccccccccccccccccccccccc'];
                EOT,
        ];

        yield 'with() calls are always split' => [
            <<<'EOT'
                <?php

                $mock
                    ->expects($this->once())
                    ->method('foo')
                    ->with('foo', [
                        'bar' => 'baz',
                    ])
                ;
                EOT,
            <<<'EOT'
                <?php

                $mock
                    ->expects($this->once())
                    ->method('foo')
                    ->with(
                        'foo',
                        ['bar' => 'baz'],
                    )
                ;
                EOT,
        ];

        yield 'arrays with more than one member are not compacted' => [
            <<<'EOT'
                <?php

                $mock
                    ->method('foo')
                    ->with('foo', [
                        'foo' => 'bar',
                        'bar' => 'baz',
                    ])
                ;
                EOT,
            <<<'EOT'
                <?php

                $mock
                    ->method('foo')
                    ->with(
                        'foo',
                        [
                            'foo' => 'bar',
                            'bar' => 'baz',
                        ],
                    )
                ;
                EOT,
        ];

        yield 'lists with comments are not changed' => [
            <<<'EOT'
                <?php

                $foo = ['foo', // comment
                    'bar'];
                EOT,
            <<<'EOT'
                <?php

                $foo = ['foo', // comment
                    'bar'];
                EOT,
        ];

        yield 'heredocs always stand alone' => [
            <<<'EOT'
                <?php

                $this->assertString(<<<'EOD'
                    foo
                    EOD);

                $this->assertSame($foo, <<<'EOD'
                    foo
                    EOD);

                $this->assertSame(<<<'EOD'
                    foo
                    EOD, $foo);

                if (true) {
                    $this->assertSame(<<<EOD
                        foo {$bar}
                        $baz

                          baz
                        EOD, $foo);
                }
                EOT,
            <<<'EOT'
                <?php

                $this->assertString(
                    <<<'EOD'
                        foo
                        EOD,
                );

                $this->assertSame(
                    $foo,
                    <<<'EOD'
                        foo
                        EOD,
                );

                $this->assertSame(
                    <<<'EOD'
                        foo
                        EOD,
                    $foo,
                );

                if (true) {
                    $this->assertSame(
                        <<<EOD
                            foo {$bar}
                            $baz

                              baz
                            EOD,
                        $foo,
                    );
                }
                EOT,
        ];

        yield 'heredocs are outdented, in arrays and in nested calls' => [
            <<<'EOT'
                <?php

                $this->assertSame(
                        <<<'EOD'
                            foo
                            EOD,
                        $foo);

                $foo = [<<<'EOD'
                    foo
                    EOD, 'bar'];

                foo(sprintf(<<<'EOD'
                    %s
                    EOD, $b));
                EOT,
            <<<'EOT'
                <?php

                $this->assertSame(
                    <<<'EOD'
                        foo
                        EOD,
                    $foo,
                );

                $foo = [
                    <<<'EOD'
                        foo
                        EOD,
                    'bar',
                ];

                foo(sprintf(
                    <<<'EOD'
                        %s
                        EOD,
                    $b,
                ));
                EOT,
        ];

        yield 'multi-line ignored calls and declarations are not changed' => [
            <<<'EOT'
                <?php

                if (foo($a, [
                    'b',
                ], $c)) {
                }

                function foo(array $options = ['foo' => [
                    'bar',
                ]], $b = 1): void
                {
                }

                #[Foo('a', [
                    'b',
                ], 'c')]
                class Bar
                {
                }

                $command->addArgument('foo', InputArgument::REQUIRED, 'The foo', [
                    'a',
                ]);

                $command->addOption('foo', null, InputOption::VALUE_NONE, 'The foo', [
                    'a',
                ]);

                ArrayUtil::arrayInsert($foo, 1, [
                    'a',
                ]);
                EOT,
            <<<'EOT'
                <?php

                if (foo($a, [
                    'b',
                ], $c)) {
                }

                function foo(array $options = ['foo' => [
                    'bar',
                ]], $b = 1): void
                {
                }

                #[Foo('a', [
                    'b',
                ], 'c')]
                class Bar
                {
                }

                $command->addArgument('foo', InputArgument::REQUIRED, 'The foo', [
                    'a',
                ]);

                $command->addOption('foo', null, InputOption::VALUE_NONE, 'The foo', [
                    'a',
                ]);

                ArrayUtil::arrayInsert($foo, 1, [
                    'a',
                ]);
                EOT,
        ];

        yield 'second argument array exception does not apply' => [
            <<<'EOT'
                <?php

                $mock->withConsecutive($foo, [
                    'a',
                ]);

                $mock->withAnyParameters($foo, [
                    'a',
                ]);

                foo('a b', [
                    'a',
                ]);

                foo($a ?? $b, [
                    'a',
                ]);
                EOT,
            <<<'EOT'
                <?php

                $mock->withConsecutive(
                    $foo,
                    ['a'],
                );

                $mock->withAnyParameters(
                    $foo,
                    ['a'],
                );

                foo(
                    'a b',
                    ['a'],
                );

                foo(
                    $a ?? $b,
                    ['a'],
                );
                EOT,
        ];

        yield 'array(), interpolated strings and inline HTML are not changed' => [
            <<<'EOT'
                <?php

                $a = array('a' => array(
                    'b',
                ), 'c');

                $b = "{$a->b($c,
                    $d)}";

                foo($a, function () { ?>
                    <p>bar</p>
                <?php }, $c);
                EOT,
            <<<'EOT'
                <?php

                $a = array('a' => array(
                    'b',
                ), 'c');

                $b = "{$a->b($c,
                    $d)}";

                foo($a, function () { ?>
                    <p>bar</p>
                <?php }, $c);
                EOT,
        ];

        yield 'empty lines, missing trailing commas and nested arrays' => [
            <<<'EOT'
                <?php

                foo(
                    $a,

                    $b, [
                        'c',
                    ]
                );

                foo(
                    $a,
                    $b
                );

                $foo = [[
                    'foo',
                ],];

                $foo = [ [
                    'foo',
                ] ];
                EOT,
            <<<'EOT'
                <?php

                foo(
                    $a,

                    $b,
                    ['c'],
                );

                foo(
                    $a,
                    $b,
                );

                $foo = [
                    [
                        'foo',
                    ],
                ];

                $foo = [
                    [
                        'foo',
                    ],
                ];
                EOT,
        ];

        yield 'multi-line conditions, arrow functions and by-reference functions are not changed' => [
            <<<'EOT'
                <?php

                if ($a) {
                } elseif (foo($a, [
                    'b',
                ], $c)) {
                }

                while (foo($a, [
                    'b',
                ], $c)) {
                }

                for ($i = foo($a, [
                    'b',
                ], $c); $i < 1; ++$i) {
                }

                foreach (foo($a, [
                    'b',
                ], $c) as $d) {
                }

                switch (foo($a, [
                    'b',
                ], $c)) {
                }

                match (foo($a, [
                    'b',
                ], $c)) {
                    default => 1,
                };

                $f = fn (array $a = ['x' => [
                    'b',
                ]], $b = 1) => $a;

                function &foo(array $options = ['foo' => [
                    'bar',
                ]], $b = 1): array
                {
                }
                EOT,
            <<<'EOT'
                <?php

                if ($a) {
                } elseif (foo($a, [
                    'b',
                ], $c)) {
                }

                while (foo($a, [
                    'b',
                ], $c)) {
                }

                for ($i = foo($a, [
                    'b',
                ], $c); $i < 1; ++$i) {
                }

                foreach (foo($a, [
                    'b',
                ], $c) as $d) {
                }

                switch (foo($a, [
                    'b',
                ], $c)) {
                }

                match (foo($a, [
                    'b',
                ], $c)) {
                    default => 1,
                };

                $f = fn (array $a = ['x' => [
                    'b',
                ]], $b = 1) => $a;

                function &foo(array $options = ['foo' => [
                    'bar',
                ]], $b = 1): array
                {
                }
                EOT,
        ];

        yield 'an array followed by an operator is not the second argument exception' => [
            <<<'EOT'
                <?php

                foo($a, [
                    'b',
                ] + $c);

                $foo = [
                ];
                EOT,
            <<<'EOT'
                <?php

                foo(
                    $a,
                    [
                        'b',
                    ] + $c,
                );

                $foo = [
                ];
                EOT,
        ];

        yield 'heredocs are never outdented further than their closing marker' => [
            <<<'EOT'
                <?php

                foo(
                        <<<'EOD'
                      foo
                  EOD,
                        $b);
                EOT,
            <<<'EOT'
                <?php

                foo(
                    <<<'EOD'
                    foo
                EOD,
                    $b,
                );
                EOT,
        ];

        yield 'heredocs with lines that start with a variable' => [
            <<<'EOT'
                <?php

                foo(<<<EOD
                $bar
                {$baz->qux($a, $b)}
                text
                EOD, $b);
                EOT,
            <<<'EOT'
                <?php

                foo(
                    <<<EOD
                    $bar
                    {$baz->qux($a, $b)}
                    text
                    EOD,
                    $b,
                );
                EOT,
        ];
    }
}
