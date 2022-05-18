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

use Contao\EasyCodingStandard\Fixer\SingleLineConfigureCommandFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class SingleLineConfigureCommandFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new SingleLineConfigureCommandFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                use Symfony\Component\Console\Command\Command;
                use Symfony\Component\Console\Input\InputArgument;
                use Symfony\Component\Console\Input\InputOption;

                class SomeCommand extends Command
                {
                    protected function configure(): void
                    {
                        $this
                            ->addArgument(
                                'foo',
                                InputArgument::REQUIRED,
                                'The argument'
                            )
                            ->addArgument('bar', InputArgument::REQUIRED)
                            ->addOption(
                                'bar',
                                null,
                                InputOption::VALUE_NONE,
                                'The option'
                            );
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                use Symfony\Component\Console\Command\Command;
                use Symfony\Component\Console\Input\InputArgument;
                use Symfony\Component\Console\Input\InputOption;

                class SomeCommand extends Command
                {
                    protected function configure(): void
                    {
                        $this
                            ->addArgument('foo', InputArgument::REQUIRED, 'The argument')
                            ->addArgument('bar', InputArgument::REQUIRED)
                            ->addOption('bar', null, InputOption::VALUE_NONE, 'The option');
                    }
                }
                EOT,
        ];
    }
}
