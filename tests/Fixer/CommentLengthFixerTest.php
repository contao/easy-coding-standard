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

use Contao\EasyCodingStandard\Fixer\CommentLengthFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class CommentLengthFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new CommentLengthFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                // This comment is shorter than 80 characters.
                // It should be on one line.
                if (true) {
                }

                // This comment is shorter than 80 characters. It should be on one line.
                if (true) {
                }

                /**
                 * This comment is shorter than 80 characters.
                 * It should be on one line.
                 */
                function foo() {
                }

                /**
                 * This comment is shorter than 80 characters. It should be on one line.
                 */
                function foo() {
                }
                EOT,
            <<<'EOT'
                <?php

                // This comment is shorter than 80 characters. It should be on one line.
                if (true) {
                }

                // This comment is shorter than 80 characters. It should be on one line.
                if (true) {
                }

                /**
                 * This comment is shorter than 80 characters. It should be on one line.
                 */
                function foo() {
                }

                /**
                 * This comment is shorter than 80 characters. It should be on one line.
                 */
                function foo() {
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                // This comment exceeds the maximum line length of 80 characters. It should be distributed accross two lines.
                if (true) {
                }

                /**
                 * This comment exceeds the maximum line length of 80 characters. It should be distributed accross two lines.
                 */
                function foo() {
                }
                EOT,
            <<<'EOT'
                <?php

                // This comment exceeds the maximum line length of 80 characters. It should be
                // distributed accross two lines.
                if (true) {
                }

                /**
                 * This comment exceeds the maximum line length of 80 characters. It should be
                 * distributed accross two lines.
                 */
                function foo() {
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                // This comment is 86 characters long. It should not be distributed accross two lines.
                if (true) {
                }

                // This comment is exactly 90 characters long. It should be distributed accross two lines.
                if (true) {
                }

                /**
                 * This comment is 86 characters long. It should not be distributed accross two lines.
                 */
                function foo() {
                }

                /**
                 * This comment is exactly 90 characters long. It should be distributed accross two lines.
                 */
                function foo() {
                }
                EOT,
            <<<'EOT'
                <?php

                // This comment is 86 characters long. It should not be distributed accross two lines.
                if (true) {
                }

                // This comment is exactly 90 characters long. It should be distributed accross
                // two lines.
                if (true) {
                }

                /**
                 * This comment is 86 characters long. It should not be distributed accross two lines.
                 */
                function foo() {
                }

                /**
                 * This comment is exactly 90 characters long. It should be distributed accross
                 * two lines.
                 */
                function foo() {
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                // Keep URLs on their own line.
                // https://contao.org
                if (true) {
                }

                /**
                 * Keep URLs on their own line.
                 * https://contao.org
                 */
                function foo() {
                }
                EOT,
            <<<'EOT'
                <?php

                // Keep URLs on their own line.
                // https://contao.org
                if (true) {
                }

                /**
                 * Keep URLs on their own line.
                 * https://contao.org
                 */
                function foo() {
                }
                EOT,
        ];

        yield [
            <<<'EOT'
                <?php

                /**
                 * Preserve unordered lists:
                 *
                 * - Foo
                 * - Bar
                 *
                 * Preserve ordered lists:
                 *
                 * 1. Foo
                 * 2. Bar
                 *
                 * Preserve code examples:
                 *
                 *     [
                 *         'foo',
                 *         'bar',
                 *     ]
                 *
                 * And anything that is indented:
                 *
                 *     framework:
                 *         mailer:
                 *             dsn: '%env(MAILER_DSN)%'
                 *
                 * That's it.
                 */
                function foo() {
                }
                EOT,
            <<<'EOT'
                <?php

                /**
                 * Preserve unordered lists:
                 *
                 * - Foo
                 * - Bar
                 *
                 * Preserve ordered lists:
                 *
                 * 1. Foo
                 * 2. Bar
                 *
                 * Preserve code examples:
                 *
                 *     [
                 *         'foo',
                 *         'bar',
                 *     ]
                 *
                 * And anything that is indented:
                 *
                 *     framework:
                 *         mailer:
                 *             dsn: '%env(MAILER_DSN)%'
                 *
                 * That's it.
                 */
                function foo() {
                }
                EOT,
        ];
    }
}
