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
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                interface FooInterface
                {
                    public function bar(object|FooService|BarService $service, iterable|int $count): ?string;
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
                }
                EOT,
            <<<'EOT'
                <?php

                interface FooInterface
                {
                    public function bar(BarService|FooService|object $service, int|iterable $count): string|null;
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
                }
                EOT,
        ];
    }
}
