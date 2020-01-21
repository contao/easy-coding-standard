<?php

declare(strict_types=1);

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
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
<?php

class Foo
{
    public function bar(
        FooService $fooService,
        BarService $barService,
        array $options = [],
        Logger $logger = null
    ): Generator {
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
    public function foo(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): Generator
    {
        return function (string $key, $value) { return $key.' '.$value; };
    }
}
EOT
            ,
            <<<'EOT'
<?php

class Foo
{
    public function bar(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): Generator
    {
        return function (string $key, $value) {
            return $key.' '.$value;
        };
    }
}

class Bar
{
    public function foo(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): Generator
    {
        return function (string $key, $value) { return $key.' '.$value; };
    }
}
EOT
            ,
        ];
    }
}
