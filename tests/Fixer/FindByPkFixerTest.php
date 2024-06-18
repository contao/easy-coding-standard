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

use Contao\EasyCodingStandard\Fixer\FindByPkFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class FindByPkFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new FindByPkFixer();
        $fixer->fix($this->createMock(\SplFileInfo::class), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public static function getCodeSamples(): iterable
    {
        yield [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function findModel(int $id): void
                    {
                        $foo = Model::findByPk($id);
                        $bar = $this->getAdapter(Model::class)->findByPk($id);
                        $baz = call_user_func(Model::class, 'findByPk');
                    }

                    public function findByPk(int $id): void
                    {
                        parent::findByPk($id);
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function findModel(int $id): void
                    {
                        $foo = Model::findById($id);
                        $bar = $this->getAdapter(Model::class)->findById($id);
                        $baz = call_user_func(Model::class, 'findById');
                    }

                    public function findByPk(int $id): void
                    {
                        parent::findByPk($id);
                    }
                }
                EOT,
        ];
    }
}
