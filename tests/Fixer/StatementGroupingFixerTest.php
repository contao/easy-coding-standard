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

use Contao\EasyCodingStandard\Fixer\StatementGroupingFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class StatementGroupingFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new StatementGroupingFixer();
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

        $this->assertSame($expected, $tokens->generateCode());

        // The fixer must be idempotent
        $tokens = Tokens::fromCode($expected);
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        foreach (['(int)', '(bool)', '(float)', '(string)', '(array)', '(object)', '(int) (float)'] as $cast) {
            yield 'shared use through '.$cast.' stays together' => [
                str_replace(
                    'CAST',
                    $cast,
                    <<<'EOT'
                        <?php

                        $fixtures = self::managedEdition()->database()->fixtures();

                        $path = $fixtures->interpolate('/contao/api/dc/page/{page_microsite_home}');
                        $parent = CAST $fixtures->value('page_main_home');
                        EOT,
                ),
                str_replace(
                    'CAST',
                    $cast,
                    <<<'EOT'
                        <?php

                        $fixtures = self::managedEdition()->database()->fixtures();
                        $path = $fixtures->interpolate('/contao/api/dc/page/{page_microsite_home}');
                        $parent = CAST $fixtures->value('page_main_home');
                        EOT,
                ),
            ];
        }

        yield 'direct use through a cast removes the empty line' => [
            <<<'EOT'
                <?php

                $fixtures = self::managedEdition()->database()->fixtures();

                $parent = (int) $fixtures->value('page_main_home');
                EOT,
            <<<'EOT'
                <?php

                $fixtures = self::managedEdition()->database()->fixtures();
                $parent = (int) $fixtures->value('page_main_home');
                EOT,
        ];

        $fields = <<<'EOT'
            <?php

            $fields = &$GLOBALS['TL_DCA']['tl_cloud_emails']['fields'];
            $fields['autoreply']['value'] = $email->autoreply;
            $fields['start']['value'] = $email->start;
            $fields['stop']['value'] = $email->stop;
            $fields['message']['value'] = $email->message;
            EOT;

        yield 'nested assignments to the same array stay together' => [$fields, $fields];

        yield 'empty lines between related nested array assignments are removed' => [
            <<<'EOT'
                <?php

                $fields = &$GLOBALS['TL_DCA']['tl_cloud_emails']['fields'];
                $fields['autoreply']['value'] = $email->autoreply;

                $fields['start']['value'] = $email->start;
                $fields['stop']['value'] = $email->stop;
                $fields['message']['value'] = $email->message;
                EOT,
            $fields,
        ];

        yield 'nested assignments in different global arrays are separated' => [
            <<<'EOT'
                <?php

                $GLOBALS['TL_DCA']['tl_content']['fields']['foo']['value'] = 1;
                $GLOBALS['TL_DCA']['tl_content']['fields']['bar']['value'] = 2;
                $GLOBALS['TL_LANG']['MSC']['foo']['value'] = 3;
                EOT,
            <<<'EOT'
                <?php

                $GLOBALS['TL_DCA']['tl_content']['fields']['foo']['value'] = 1;
                $GLOBALS['TL_DCA']['tl_content']['fields']['bar']['value'] = 2;

                $GLOBALS['TL_LANG']['MSC']['foo']['value'] = 3;
                EOT,
        ];

        yield 'nested assignments in different properties of this are separated' => [
            <<<'EOT'
                <?php

                $this->fields['foo']['value'] = 1;
                $this->fields['bar']['value'] = 2;
                $this->options['foo']['value'] = 3;
                EOT,
            <<<'EOT'
                <?php

                $this->fields['foo']['value'] = 1;
                $this->fields['bar']['value'] = 2;

                $this->options['foo']['value'] = 3;
                EOT,
        ];

        yield 'a variable name in an array key is not a variable use' => [
            <<<'EOT'
                <?php

                $container = new Container();
                foo($items['$container']);
                EOT,
            <<<'EOT'
                <?php

                $container = new Container();

                foo($items['$container']);
                EOT,
        ];

        $unchanged = <<<'EOT'
            <?php

            $items['foo'.'bar'] = new Foo();
            register($items['foo'.'bar']);
            EOT;

        yield 'a call using an assigned element with a compound key stays attached' => [$unchanged, $unchanged];

        yield 'brackets in string keys do not affect the parent subject' => [
            <<<'EOT'
                <?php

                $config['a[b'] = new Foo();

                $config['other'] = 2;

                $config['c]d'] = 3;
                EOT,
            <<<'EOT'
                <?php

                $config['a[b'] = new Foo();
                $config['other'] = 2;
                $config['c]d'] = 3;
                EOT,
        ];

        $unchanged = <<<'EOT'
            <?php

            foo();
            $class::bar();
            $class::$method();
            bar();
            EOT;

        yield 'dynamic static calls belong to the calls group' => [$unchanged, $unchanged];

        $unchanged = <<<'EOT'
            <?php

            $container = new Container();
            foo("Container: {$container}");
            EOT;

        yield 'interpolated variables count as variable uses' => [$unchanged, $unchanged];

        yield 'a static property is not a local variable use' => [
            <<<'EOT'
                <?php

                $container = new Container();
                register(Registry::$container);
                EOT,
            <<<'EOT'
                <?php

                $container = new Container();

                register(Registry::$container);
                EOT,
        ];

        $unchanged = <<<'EOT'
            <?php

            self::$container['foo'.'bar'] = new Container();
            register(self::$container['foo'.'bar']);
            EOT;

        yield 'a call using an assigned static property stays attached' => [$unchanged, $unchanged];

        yield 'a variable name in a string literal is not a variable use in a call' => [
            <<<'EOT'
                <?php

                $container = new Container();
                foo('$container');
                EOT,
            <<<'EOT'
                <?php

                $container = new Container();

                foo('$container');
                EOT,
        ];

        yield 'a variable name in a string literal does not break shared use' => [
            <<<'EOT'
                <?php

                $a = $factory->create('a');

                $b = $factory->create('b');
                $this->assertSame('$b', 'literal');
                EOT,
            <<<'EOT'
                <?php

                $a = $factory->create('a');
                $b = $factory->create('b');

                $this->assertSame('$b', 'literal');
                EOT,
        ];

        yield 'direct use of an assigned array element removes the empty line' => [
            <<<'EOT'
                <?php

                $items['foo'] = new Foo();

                $value = $items['foo']->getValue();
                EOT,
            <<<'EOT'
                <?php

                $items['foo'] = new Foo();
                $value = $items['foo']->getValue();
                EOT,
        ];

        yield 'a call using an assigned array element with a string key stays attached' => [
            <<<'EOT'
                <?php

                $items['foo'] = new Foo();
                register($items['foo']);
                EOT,
            <<<'EOT'
                <?php

                $items['foo'] = new Foo();
                register($items['foo']);
                EOT,
        ];

        yield 'a standalone comment after a trailing comment is formatted' => [
            <<<'EOT'
                <?php

                $x = 1; // trailing
                // standalone

                $y = 2;
                EOT,
            <<<'EOT'
                <?php

                $x = 1; // trailing

                // standalone
                $y = 2;
                EOT,
        ];

        yield 'a deprecation with a trailing comma is separated from calls' => [
            <<<'EOT'
                <?php

                foo();
                trigger_error('Deprecated', E_USER_DEPRECATED,);
                bar();
                EOT,
            <<<'EOT'
                <?php

                foo();

                trigger_error('Deprecated', E_USER_DEPRECATED,);

                bar();
                EOT,
        ];

        $unchanged = <<<'EOT'
            <?php

            $cron = new Cron(
                $repository,
                $cache,
            );
            $cron->addCronJob($job);
            EOT;

        yield 'a multi-line declaration and its direct usage stay together' => [$unchanged, $unchanged];

        yield 'related group is separated' => [
            <<<'EOT'
                <?php

                $request = new Request();
                $requestStack = new RequestStack();
                $requestStack->push($request);
                $environment = new Environment();
                $hasScope = false;
                EOT,
            <<<'EOT'
                <?php

                $request = new Request();

                $requestStack = new RequestStack();
                $requestStack->push($request);

                $environment = new Environment();
                $hasScope = false;
                EOT,
        ];

        $payload = <<<'EOT'
            <?php

            $altcha = $this->getAltcha($repository);
            $challenge = $altcha->createChallenge('salt?expires='.(time() - 600), 42);

            $payload = $challenge->toArray();
            $payload['number'] = 42;
            $payload['challenge'] = 'foobar';
            EOT;

        yield 'direct use that starts an array group keeps its empty line' => [$payload, $payload];

        yield 'direct use that starts an array group gets an empty line' => [
            str_replace("\n\n\$payload", "\n\$payload", $payload),
            $payload,
        ];

        $unchanged = <<<'EOT'
            <?php

            $challenge = $altcha->createChallenge();
            $payload = $challenge->toArray();
            consume($payload);
            EOT;

        yield 'direct use followed by argument use stays together' => [$unchanged, $unchanged];

        yield 'only adjacent statements are compared' => [
            <<<'EOT'
                <?php

                $requestStack = new RequestStack();
                $environment = new Environment();
                $requestStack->push($request);
                EOT,
            <<<'EOT'
                <?php

                $requestStack = new RequestStack();
                $environment = new Environment();
                $requestStack->push($request);
                EOT,
        ];

        yield 'deprecations, function calls and static calls' => [
            <<<'EOT'
                <?php

                $request = new Request();
                $environment = new Environment();
                trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');
                System::loadLanguageFile('default');
                self::bootKernel();
                unset($foo);
                $hasScope = false;
                EOT,
            <<<'EOT'
                <?php

                $request = new Request();
                $environment = new Environment();

                trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');

                System::loadLanguageFile('default');
                self::bootKernel();
                unset($foo);

                $hasScope = false;
                EOT,
        ];

        yield 'mock setup' => [
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

        yield 'comments break the sequence' => [
            <<<'EOT'
                <?php

                $mock = $this->createMock(Foo::class);
                $mock
                    ->method('isFoo')
                    ->willReturn(true)
                ;

                // But add a new line when it is used
                $mock->isFoo();
                $foo = 'Foo';
                // Empty line before the comment

                $bar = 'Bar';
                EOT,
            <<<'EOT'
                <?php

                $mock = $this->createMock(Foo::class);
                $mock
                    ->method('isFoo')
                    ->willReturn(true)
                ;

                // But add a new line when it is used
                $mock->isFoo();
                $foo = 'Foo';

                // Empty line before the comment
                $bar = 'Bar';
                EOT,
        ];

        yield 'multi-line statements' => [
            <<<'EOT'
                <?php

                $prefix = 'contao_api_'.$name;
                $operations = [
                    $prefix.'_get_collection' => $this->createCollectionOperation($path),
                    $prefix.'_move' => $this->createMoveOperation($path),
                ];
                $metadata = ['filesystem' => $name, 'relation_filesystem' => $tags['api_relation_filesystem'] ?? $name];
                EOT,
            <<<'EOT'
                <?php

                $prefix = 'contao_api_'.$name;

                $operations = [
                    $prefix.'_get_collection' => $this->createCollectionOperation($path),
                    $prefix.'_move' => $this->createMoveOperation($path),
                ];

                $metadata = ['filesystem' => $name, 'relation_filesystem' => $tags['api_relation_filesystem'] ?? $name];
                EOT,
        ];

        yield 'stubs' => [
            <<<'EOT'
                <?php

                $names = $this->createStub(PropertyNameCollectionFactoryInterface::class);
                $names
                    ->method('create')
                    ->willReturn(new PropertyNameCollection(['path']))
                ;
                $classes = $this->createStub(ResourceClassResolverInterface::class);

                $classes
                    ->method('isResourceClass')
                    ->willReturn(true)
                ;
                $links = new LinkFactory($names, $properties, $classes);
                EOT,
            <<<'EOT'
                <?php

                $names = $this->createStub(PropertyNameCollectionFactoryInterface::class);
                $names
                    ->method('create')
                    ->willReturn(new PropertyNameCollection(['path']))
                ;

                $classes = $this->createStub(ResourceClassResolverInterface::class);
                $classes
                    ->method('isResourceClass')
                    ->willReturn(true)
                ;

                $links = new LinkFactory($names, $properties, $classes);
                EOT,
        ];

        yield 'direct use' => [
            <<<'EOT'
                <?php

                $serializer = new Serializer([$this->createNormalizer(), new RelationReferenceNormalizer()]);
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
                $normalized = $serializer->normalize($record, 'jsonld');

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

                $serializer = new Serializer([$this->createNormalizer(), new RelationReferenceNormalizer()]);

                $normalized = $serializer->normalize($record, 'jsonld');
                EOT,
            <<<'EOT'
                <?php

                $serializer = new Serializer([$this->createNormalizer(), new RelationReferenceNormalizer()]);

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

                $normalized = $serializer->normalize($record, 'jsonld');

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

                $serializer = new Serializer([$this->createNormalizer(), new RelationReferenceNormalizer()]);
                $normalized = $serializer->normalize($record, 'jsonld');
                EOT,
        ];

        yield 'assertions' => [
            <<<'EOT'
                <?php

                $operation = $operations[$name];
                $this->assertSame('/files/{pathOrUuid}', $operation->getUriTemplate());
                $this->assertSame(['pathOrUuid' => '.+'], $operation->getRequirements());
                $link = $operation->getUriVariables()['pathOrUuid'];
                $this->assertSame(['path'], $link->getIdentifiers());
                $this->assertSame(VirtualFilesystemItem::class, $link->getFromClass());
                EOT,
            <<<'EOT'
                <?php

                $operation = $operations[$name];
                $this->assertSame('/files/{pathOrUuid}', $operation->getUriTemplate());
                $this->assertSame(['pathOrUuid' => '.+'], $operation->getRequirements());

                $link = $operation->getUriVariables()['pathOrUuid'];
                $this->assertSame(['path'], $link->getIdentifiers());
                $this->assertSame(VirtualFilesystemItem::class, $link->getFromClass());
                EOT,
        ];

        yield 'expectations and static assertions in a non-test class' => [
            <<<'EOT'
                <?php

                class Foo
                {
                    public function bar(): void
                    {
                        $this->expectException(\InvalidArgumentException::class);
                        $this->expectExceptionMessage('Invalid');
                        $service->run();
                        self::assertTrue(true);
                        static::assertFalse(false);
                        $result = $service->run();
                        $this->assertTrue($result);
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                class Foo
                {
                    public function bar(): void
                    {
                        $this->expectException(\InvalidArgumentException::class);
                        $this->expectExceptionMessage('Invalid');

                        $service->run();

                        self::assertTrue(true);
                        static::assertFalse(false);

                        $result = $service->run();
                        $this->assertTrue($result);
                    }
                }
                EOT,
        ];

        yield 'array keys are part of the subject' => [
            <<<'EOT'
                <?php

                $GLOBALS['BE_FFL']['customFiles'] = $widget::class;

                $GLOBALS['TL_DCA']['tl_content']['fields'] = [
                    'files' => ['inputType' => 'customFiles', 'eval' => ['multiple' => true]],
                ];
                $GLOBALS['TL_LANG']['MSC']['foo'] = 'Foo';
                $GLOBALS['TL_LANG']['MSC']['bar'] = 'Bar';

                $config = [];

                $config['foo'] = 'Foo';
                $config['bar'] = 'Bar';
                $items[] = 'a';

                $items[] = 'b';
                EOT,
            <<<'EOT'
                <?php

                $GLOBALS['BE_FFL']['customFiles'] = $widget::class;

                $GLOBALS['TL_DCA']['tl_content']['fields'] = [
                    'files' => ['inputType' => 'customFiles', 'eval' => ['multiple' => true]],
                ];

                $GLOBALS['TL_LANG']['MSC']['foo'] = 'Foo';
                $GLOBALS['TL_LANG']['MSC']['bar'] = 'Bar';

                $config = [];
                $config['foo'] = 'Foo';
                $config['bar'] = 'Bar';

                $items[] = 'a';
                $items[] = 'b';
                EOT,
        ];

        yield 'multi-line declarations and multi-line key assignments are not related' => [
            <<<'EOT'
                <?php

                $operation = $operation->withParameters([
                    'foo' => 'bar',
                ]);
                $operations[$name] = $operation
                    ->withClass(UserTemplate::class)
                    ->withShortName('UserTemplate')
                ;
                $links = clone ($response->getLinks() ?? new \ArrayObject());
                $links['move'] = new Link(
                    operationId: $operationId,
                    description: 'Move',
                );
                $config = [];
                $config['foo'] = 'Foo';
                EOT,
            <<<'EOT'
                <?php

                $operation = $operation->withParameters([
                    'foo' => 'bar',
                ]);

                $operations[$name] = $operation
                    ->withClass(UserTemplate::class)
                    ->withShortName('UserTemplate')
                ;

                $links = clone ($response->getLinks() ?? new \ArrayObject());

                $links['move'] = new Link(
                    operationId: $operationId,
                    description: 'Move',
                );

                $config = [];
                $config['foo'] = 'Foo';
                EOT,
        ];

        yield 'empty lines between unrelated statements are kept' => [
            <<<'EOT'
                <?php

                $indentComment = $this->getIndent($tokens, $prevComment, false);
                $replacement = rtrim($indentComment, "\t ").$indent;
                $i = $prevComment - 1;

                $tokens->offsetSet($prevComment - 1, new Token([T_WHITESPACE, $replacement]));

                $newTokens = [];

                $tokens->overrideRange($isArrayStart, $emptyEnd, $newTokens);

                $index = $emptyEnd + 1;

                System::loadLanguageFile('default');

                self::bootKernel();

                $this->assertTrue($a);
                $this->assertFalse($b);

                $resources = iterator_to_array($collection);

                $operation = iterator_to_array($resources[0]->getOperations());
                $this->assertTrue($operation->getPaginationClientItemsPerPage());
                $this->assertSame($expectedMaximum, $operation->getPaginationMaximumItemsPerPage());

                $this->assertResource($resources[0], 'Article', 'tl_article', '/dc/article', 'article', true);
                $this->assertResource($resources[1], 'Content', 'tl_content', '/dc/article/{article_id}/content', 'article_content', true);
                $this->converters = new WidgetConverterRegistry([$converter]);

                $GLOBALS['TL_DCA']['tl_content']['fields']['rows'] = ['inputType' => 'customRows'];

                $controller = $this->createAdapterStub(['loadDataContainer']);
                EOT,
            <<<'EOT'
                <?php

                $indentComment = $this->getIndent($tokens, $prevComment, false);
                $replacement = rtrim($indentComment, "\t ").$indent;
                $i = $prevComment - 1;

                $tokens->offsetSet($prevComment - 1, new Token([T_WHITESPACE, $replacement]));

                $newTokens = [];

                $tokens->overrideRange($isArrayStart, $emptyEnd, $newTokens);

                $index = $emptyEnd + 1;

                System::loadLanguageFile('default');

                self::bootKernel();

                $this->assertTrue($a);
                $this->assertFalse($b);

                $resources = iterator_to_array($collection);

                $operation = iterator_to_array($resources[0]->getOperations());
                $this->assertTrue($operation->getPaginationClientItemsPerPage());
                $this->assertSame($expectedMaximum, $operation->getPaginationMaximumItemsPerPage());

                $this->assertResource($resources[0], 'Article', 'tl_article', '/dc/article', 'article', true);
                $this->assertResource($resources[1], 'Content', 'tl_content', '/dc/article/{article_id}/content', 'article_content', true);

                $this->converters = new WidgetConverterRegistry([$converter]);

                $GLOBALS['TL_DCA']['tl_content']['fields']['rows'] = ['inputType' => 'customRows'];

                $controller = $this->createAdapterStub(['loadDataContainer']);
                EOT,
        ];

        yield 'unset, parse_str and calls using the declared variable' => [
            <<<'EOT'
                <?php

                $data = $this->toArray($data);
                unset($data['@context'], $data['@id'], $data['@type']);

                $data = $this->normalizeInputReferences($data);
                $url = $this->urlAnalyzer->getEditUrl(/* ... */);
                parse_str(parse_url($url ?? '', PHP_URL_QUERY) ?? '', $query);
                $foo = 'Foo';
                parse_url($bar);
                $container = $this->getContainerWithContaoConfiguration();
                System::setContainer($container);
                $containers = [];
                System::setContainer($container);
                $request = new Request();
                foo($requestStack);
                EOT,
            <<<'EOT'
                <?php

                $data = $this->toArray($data);
                unset($data['@context'], $data['@id'], $data['@type']);

                $data = $this->normalizeInputReferences($data);
                $url = $this->urlAnalyzer->getEditUrl(/* ... */);
                parse_str(parse_url($url ?? '', PHP_URL_QUERY) ?? '', $query);

                $foo = 'Foo';
                parse_url($bar);

                $container = $this->getContainerWithContaoConfiguration();
                System::setContainer($container);

                $containers = [];

                System::setContainer($container);

                $request = new Request();

                foo($requestStack);
                EOT,
        ];

        yield 'properties, shared variables and repeated functions' => [
            <<<'EOT'
                <?php

                $page = $this->createClassWithPropertiesStub(PageModel::class, ['dateFormat']);
                $page->dateFormat = 'Y-m-d';
                $page->timeFormat = 'H:i';
                $optIn = $this->framework->createInstance(OptInModel::class);
                $optIn->tstamp = time();
                $optIn->token = $token;
                $optIn->save();
                $this->foo = 'Foo';
                $this->bar = 'Bar';
                $parameterBag = $container->getParameterBag();
                $projectDir = $parameterBag->resolveValue($parameterBag->get('kernel.project_dir'));
                $uploadDir = $parameterBag->resolveValue($parameterBag->get('contao.upload_path'));
                $a = $this->createStub(A::class);
                $b = $this->createStub(B::class);
                $substituteCharacter = mb_substitute_character();
                mb_substitute_character(0xFFFD);
                $length = strlen($foo);
                trim($bar);
                EOT,
            <<<'EOT'
                <?php

                $page = $this->createClassWithPropertiesStub(PageModel::class, ['dateFormat']);
                $page->dateFormat = 'Y-m-d';
                $page->timeFormat = 'H:i';

                $optIn = $this->framework->createInstance(OptInModel::class);
                $optIn->tstamp = time();
                $optIn->token = $token;
                $optIn->save();

                $this->foo = 'Foo';
                $this->bar = 'Bar';

                $parameterBag = $container->getParameterBag();
                $projectDir = $parameterBag->resolveValue($parameterBag->get('kernel.project_dir'));
                $uploadDir = $parameterBag->resolveValue($parameterBag->get('contao.upload_path'));

                $a = $this->createStub(A::class);
                $b = $this->createStub(B::class);
                $substituteCharacter = mb_substitute_character();
                mb_substitute_character(0xFFFD);

                $length = strlen($foo);

                trim($bar);
                EOT,
        ];

        yield 'a variable used in the next statement begins a new block' => [
            <<<'EOT'
                <?php

                $factory = new MenuFactory();
                $system = $factory->createItem('system');
                $menu = $factory->createItem('mainMenu');
                $menu->addChild($system);
                $controller = $this->contaoFramework->getAdapter(Controller::class);
                $contentModel = $this->contaoFramework->getAdapter(ContentModel::class);
                $template = $this->contaoFramework->getAdapter(Template::class);
                $event['details'] = $template->once(
                    static fn (): string => $contentModel->foo($controller),
                );
                $processed[] = $file->getBasename();

                $table = $file->getBasename('.php');
                $extract = DcaExtractor::getInstance($table);
                EOT,
            <<<'EOT'
                <?php

                $factory = new MenuFactory();
                $system = $factory->createItem('system');

                $menu = $factory->createItem('mainMenu');
                $menu->addChild($system);

                $controller = $this->contaoFramework->getAdapter(Controller::class);
                $contentModel = $this->contaoFramework->getAdapter(ContentModel::class);

                $template = $this->contaoFramework->getAdapter(Template::class);
                $event['details'] = $template->once(
                    static fn (): string => $contentModel->foo($controller),
                );

                $processed[] = $file->getBasename();

                $table = $file->getBasename('.php');
                $extract = DcaExtractor::getInstance($table);
                EOT,
        ];

        yield 'method calls on $this are function calls' => [
            <<<'EOT'
                <?php

                $this->iniSet('date.timezone', (string) $config->get('timeZone'));
                date_default_timezone_set((string) $config->get('timeZone'));
                $request = new Request();
                $this->handle();
                $this->foo->bar();
                $this->loadFixtures();
                $this->bootKernel();
                System::loadLanguageFile('default');
                $container = $this->getContainer();
                $this->setContainer($container);
                EOT,
            <<<'EOT'
                <?php

                $this->iniSet('date.timezone', (string) $config->get('timeZone'));
                date_default_timezone_set((string) $config->get('timeZone'));

                $request = new Request();

                $this->handle();

                $this->foo->bar();

                $this->loadFixtures();
                $this->bootKernel();
                System::loadLanguageFile('default');

                $container = $this->getContainer();
                $this->setContainer($container);
                EOT,
        ];

        yield 'closure calls are function calls' => [
            <<<'EOT'
                <?php

                $sortRecursive = static function (&$node) use (&$sortRecursive): void {
                    ksort($node);
                };
                $sortRecursive($prefixTree);
                ksort($notPrefixed);
                $foo = 'Foo';
                $callback($foo);
                EOT,
            <<<'EOT'
                <?php

                $sortRecursive = static function (&$node) use (&$sortRecursive): void {
                    ksort($node);
                };

                $sortRecursive($prefixTree);
                ksort($notPrefixed);

                $foo = 'Foo';
                $callback($foo);
                EOT,
        ];

        yield 'closures and control structures' => [
            <<<'EOT'
                <?php

                $foo = function () {
                    $a = 1;
                    foo();
                };
                $bar = 2;

                foreach ($items as $item) {
                    $a = 1;
                    $b = 2;
                }
                $c = 3;
                if ($c) {
                    return;
                }
                $d = 4;

                switch ($foo) {
                    case 1:
                        $a = 1;
                        foo();
                        break;
                }
                EOT,
            <<<'EOT'
                <?php

                $foo = function () {
                    $a = 1;

                    foo();
                };

                $bar = 2;

                foreach ($items as $item) {
                    $a = 1;
                    $b = 2;
                }

                $c = 3;
                if ($c) {
                    return;
                }

                $d = 4;

                switch ($foo) {
                    case 1:
                        $a = 1;

                        foo();
                        break;
                }
                EOT,
        ];

        yield 'configuration file' => [
            <<<'EOT'
                <?php

                use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

                return static function (ContainerConfigurator $container): void {
                    $services = $container->services();
                    $services->defaults()->autowire();

                    $services->set(Foo::class);
                    $container->parameters()->set('foo', 'bar');
                };
                EOT,
            <<<'EOT'
                <?php

                use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

                return static function (ContainerConfigurator $container): void {
                    $services = $container->services();
                    $services->defaults()->autowire();

                    $services->set(Foo::class);

                    $container->parameters()->set('foo', 'bar');
                };
                EOT,
        ];

        yield 'blocks with a single statement' => [
            <<<'EOT'
                <?php

                function foo()
                {
                    if ($a) {
                        return;
                    }
                    $b = 1;
                }

                function bar()
                {
                    // Comment

                    $b = 1;
                }

                function baz()
                {
                    $a = 1;
                }
                EOT,
            <<<'EOT'
                <?php

                function foo()
                {
                    if ($a) {
                        return;
                    }

                    $b = 1;
                }

                function bar()
                {
                    // Comment
                    $b = 1;
                }

                function baz()
                {
                    $a = 1;
                }
                EOT,
        ];

        yield 'comments' => [
            <<<'EOT'
                <?php

                $a = 1; // end of line comment
                $b = 2;

                if ($a) {
                }
                // Comment after a closing brace
                $c = 3;
                // First line
                // Second line

                $d = 4;

                switch ($a) {
                    case 1:
                        // Comment after a case label
                        foo();
                        break;
                }
                EOT,
            <<<'EOT'
                <?php

                $a = 1; // end of line comment
                $b = 2;

                if ($a) {
                }

                // Comment after a closing brace
                $c = 3;

                // First line
                // Second line
                $d = 4;

                switch ($a) {
                    case 1:
                        // Comment after a case label
                        foo();
                        break;
                }
                EOT,
        ];

        yield 'file header comments after the open tag are not changed' => [
            <<<'EOT'
                <?php

                // File header

                $a = 1;
                EOT,
            <<<'EOT'
                <?php

                // File header

                $a = 1;
                EOT,
        ];

        yield 'categories' => [
            <<<'EOT'
                <?php

                $a = 'a';
                $a .= 'b';
                foo();
                self::$foo = 1;
                [$b, $c] = $d;
                list($e, $f) = $g;
                $fn($a)->foo();
                \trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');
                @trigger_error('Bar is deprecated.', E_USER_DEPRECATED);
                trigger_error('Baz is deprecated.', \E_USER_DEPRECATED);
                @trigger_error('Baz', E_USER_WARNING);
                @unlink($file);
                $h = 1;
                EOT,
            <<<'EOT'
                <?php

                $a = 'a';
                $a .= 'b';

                foo();

                self::$foo = 1;
                [$b, $c] = $d;
                list($e, $f) = $g;

                $fn($a)->foo();

                \trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');

                @trigger_error('Bar is deprecated.', E_USER_DEPRECATED);

                trigger_error('Baz is deprecated.', \E_USER_DEPRECATED);

                @trigger_error('Baz', E_USER_WARNING);
                @unlink($file);

                $h = 1;
                EOT,
        ];

        yield 'statements on the same line and case/default labels' => [
            <<<'EOT'
                <?php

                $a = 1; foo();
                $b = 2;

                switch ($a) {
                    case 1:
                        $c = 1;
                        foo();
                        break;

                    default:
                        $d = 1;
                        bar();
                }
                EOT,
            <<<'EOT'
                <?php

                $a = 1; foo();

                $b = 2;

                switch ($a) {
                    case 1:
                        $c = 1;

                        foo();
                        break;

                    default:
                        $d = 1;

                        bar();
                }
                EOT,
        ];

        yield 'properties of $this and multi-line properties are not related' => [
            <<<'EOT'
                <?php

                $this->foo = 1;

                $this->bar = 2;
                $this->getFoo()->bar = 1;

                $this->baz = 3;
                $page = new PageModel();
                $page->title = 'Foo';
                $page->options = [
                    'foo' => 'bar',
                ];
                EOT,
            <<<'EOT'
                <?php

                $this->foo = 1;

                $this->bar = 2;
                $this->getFoo()->bar = 1;

                $this->baz = 3;

                $page = new PageModel();
                $page->title = 'Foo';

                $page->options = [
                    'foo' => 'bar',
                ];
                EOT,
        ];

        yield 'multi-line assertions and nullsafe chains' => [
            <<<'EOT'
                <?php

                $foo = 1;
                $this->assertSame(
                    [1, 2],
                    $foo,
                );
                $this->assertTrue($bar);
                $client = $this->getClient();

                $client?->request('GET', '/');
                $client?->followRedirects();
                EOT,
            <<<'EOT'
                <?php

                $foo = 1;

                $this->assertSame(
                    [1, 2],
                    $foo,
                );

                $this->assertTrue($bar);

                $client = $this->getClient();
                $client?->request('GET', '/');
                $client?->followRedirects();
                EOT,
        ];

        yield 'multi-line nullsafe chains stay attached and are separated from usage' => [
            <<<'EOT'
                <?php

                $mock = $this->createMock(Foo::class);

                $mock
                    ?->method('isFoo')
                    ?->willReturn(true)
                ;

                $mock
                    ?->method('isBar')
                    ?->willReturn(false)
                ;
                $mock?->isFoo();
                EOT,
            <<<'EOT'
                <?php

                $mock = $this->createMock(Foo::class);
                $mock
                    ?->method('isFoo')
                    ?->willReturn(true)
                ;
                $mock
                    ?->method('isBar')
                    ?->willReturn(false)
                ;

                $mock?->isFoo();
                EOT,
        ];

        yield 'a variable that is only used as an argument does not split the group' => [
            <<<'EOT'
                <?php

                $schemaManager = new JsonLdManager(new ResponseContext());
                $graph1 = $schemaManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG);
                $graph2 = $schemaManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG);
                $this->assertSame($graph1, $graph2);

                $category = $input->getArgument('category');
                $addPalette = $input->getArgument('add-palette');
                $name = $input->getArgument('element-class');
                $className = Str::asClassName($name);
                EOT,
            <<<'EOT'
                <?php

                $schemaManager = new JsonLdManager(new ResponseContext());
                $graph1 = $schemaManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG);
                $graph2 = $schemaManager->getGraphForSchema(JsonLdManager::SCHEMA_ORG);
                $this->assertSame($graph1, $graph2);

                $category = $input->getArgument('category');
                $addPalette = $input->getArgument('add-palette');
                $name = $input->getArgument('element-class');
                $className = Str::asClassName($name);
                EOT,
        ];

        yield 'a multi-line declaration is not related to a declaration of the same variable' => [
            <<<'EOT'
                <?php

                $query = <<<SQL
                    SELECT username
                    FROM tl_member
                    SQL;
                $query = $this->connection->getDatabasePlatform()->modifyLimitQuery($query, 20);
                EOT,
            <<<'EOT'
                <?php

                $query = <<<SQL
                    SELECT username
                    FROM tl_member
                    SQL;

                $query = $this->connection->getDatabasePlatform()->modifyLimitQuery($query, 20);
                EOT,
        ];

        yield 'expectations and static assertions directly after an assignment' => [
            <<<'EOT'
                <?php

                $foo = 1;
                $this->expectException(\InvalidArgumentException::class);
                $bar = 2;
                self::assertTrue(true);
                $baz = 3;
                static::assertFalse(false);
                EOT,
            <<<'EOT'
                <?php

                $foo = 1;
                $this->expectException(\InvalidArgumentException::class);

                $bar = 2;
                self::assertTrue(true);

                $baz = 3;
                static::assertFalse(false);
                EOT,
        ];

        yield 'a function that is called again with a leading backslash' => [
            <<<'EOT'
                <?php

                $character = \mb_substitute_character();
                \mb_substitute_character(0xFFFD);
                EOT,
            <<<'EOT'
                <?php

                $character = \mb_substitute_character();
                \mb_substitute_character(0xFFFD);
                EOT,
        ];

        yield 'a deprecation is always separated, also from unset()' => [
            <<<'EOT'
                <?php

                trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');
                unset($foo);
                EOT,
            <<<'EOT'
                <?php

                trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');

                unset($foo);
                EOT,
        ];

        yield 'static properties and destructuring that are assigned a closure or match' => [
            <<<'EOT'
                <?php

                $a = 1;
                Foo::$bar = function () {
                    return 1;
                };
                $b = 2;
                static::$baz = function () {
                    return 1;
                };
                $c = 3;
                \Foo::$qux = function () {
                    return 1;
                };
                $d = 4;
                [$e, $f] = match ($a) {
                    1 => [1, 2],
                    default => [3, 4],
                };
                $g = 5;
                list($h, $i) = match ($a) {
                    default => [3, 4],
                };
                $j = 6;
                EOT,
            <<<'EOT'
                <?php

                $a = 1;

                Foo::$bar = function () {
                    return 1;
                };

                $b = 2;

                static::$baz = function () {
                    return 1;
                };

                $c = 3;

                \Foo::$qux = function () {
                    return 1;
                };

                $d = 4;

                [$e, $f] = match ($a) {
                    1 => [1, 2],
                    default => [3, 4],
                };

                $g = 5;

                list($h, $i) = match ($a) {
                    default => [3, 4],
                };

                $j = 6;
                EOT,
        ];

        $statements = [];

        foreach (['+=', '-=', '*=', '/=', '.=', '%=', '**=', '??=', '&=', '|=', '^=', '<<=', '>>='] as $operator) {
            $statements[] = 'foo();';
            $statements[] = '$a '.$operator.' 1;';
        }

        yield 'assignment operators' => [
            "<?php\n\n".implode("\n", $statements),
            "<?php\n\n".implode("\n\n", $statements),
        ];

        $unchanged = <<<'EOT'
            <?php

            $normalizer = $this->createNormalizer();

            $result = $normalizer->denormalize(
                ['published' => true, 'tags' => ['new']],
                DataContainerRecord::class,
                context: ['contao_table' => 'tl_content', AbstractNormalizer::OBJECT_TO_POPULATE => $record],
            );

            $normalizer = new DataContainerRecordNormalizer(
                new DataContainerRelationResolver(
                    $connection,
                    $metadataFactory,
                    $router,
                    $this->createStub(DcaHierarchy::class),
                ),
                $contextBuilder,
            );

            $normalizer->setNormalizer(new Serializer([new RelationReferenceNormalizer()]));

            $options ??= Options::create();

            $options = $options->withSetting(
                Options::OPTION_TEMPORARY_ACCESS_INFORMATION,
                TemporaryAccessOption::createFromContent($ttl, $content),
            );

            $operation->setUrl($url);

            $operation['attributes']
                ->set('data-controller', 'contao--clipboard')
            ;

            $cron = new Cron(
                fn () => $this->createStub(CronJobRepository::class),
                fn () => $this->createEntityManagerMock(false),
                $this->createStub(CacheItemPoolInterface::class),
            );

            $cron->addCronJob(new CronJob($cronjob, '@hourly'));
            $cron->run(Cron::SCOPE_CLI);
            EOT;

        yield 'empty lines of multi-line statements and of statements that are not assignments are kept' => [$unchanged, $unchanged];
    }
}
