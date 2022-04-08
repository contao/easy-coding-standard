<?php

declare(strict_types=1);

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
                    public function bar(object|FooService|BarService $service, iterable|int $count): null|string|int;
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
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                interface FooInterface
                {
                    public function bar(BarService|FooService|object $service, int|iterable $count): int|string|null;
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
                    }
                }
                EOT,
        ];
    }
}
