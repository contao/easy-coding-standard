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

use Contao\EasyCodingStandard\Fixer\TypeHintOrderFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class TypeHintOrderFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new TypeHintOrderFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
        $this->assertTrue($tokens->isMonolithicPhp());
    }

    public function testHeaderCommentFixerCanProcessReorderedTypeHint(): void
    {
        $tokens = Tokens::fromCode(
            <<<'EOT'
                <?php

                class Foo
                {
                    public function bar(string|FooService|null $service): void
                    {
                    }
                }
                EOT,
        );

        $file = $this->createMock('SplFileInfo');
        (new TypeHintOrderFixer())->fix($file, $tokens);

        $headerFixer = new HeaderCommentFixer();
        $headerFixer->configure([
            'header' => 'This file is part of Contao.',
        ]);

        $this->assertTrue($headerFixer->isCandidate($tokens));

        $headerFixer->fix($file, $tokens);

        $this->assertStringContainsString('This file is part of Contao.', $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        yield [
            <<<'EOT'
                <?php

                interface FooInterface
                {
                    public function bar(object|FooService|BarService $service, iterable|int $count): ?string;

                    public function getCallable(): null|callable;

                    public function getStatic(): null|static;
                }

                class Foo implements FooInterface
                {
                    public function __construct(
                        private null|FooService $fooService = null,
                        private ?BarService $barService = null,
                    ) {
                    }

                    public function bar(object|FooService|BarService $service, iterable|int $count): null|string|int
                    {
                        $foo = function (string|int $id): ?FooService {
                        };

                        $foo = function (string|int $id) use ($count): ?FooService {
                        };

                        $bar = fn (string|int $id): ?FooService => null;
                    }

                    public function getCallable(): null|callable
                    {
                    }

                    public function getStatic(): null|static
                    {
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                interface FooInterface
                {
                    public function bar(BarService|FooService|object $service, int|iterable $count): string|null;

                    public function getCallable(): callable|null;

                    public function getStatic(): static|null;
                }

                class Foo implements FooInterface
                {
                    public function __construct(
                        private FooService|null $fooService = null,
                        private BarService|null $barService = null,
                    ) {
                    }

                    public function bar(BarService|FooService|object $service, int|iterable $count): int|string|null
                    {
                        $foo = function (int|string $id): FooService|null {
                        };

                        $foo = function (int|string $id) use ($count): FooService|null {
                        };

                        $bar = fn (int|string $id): FooService|null => null;
                    }

                    public function getCallable(): callable|null
                    {
                    }

                    public function getStatic(): static|null
                    {
                    }
                }
                EOT,
        ];
    }
}
