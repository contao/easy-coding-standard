# EasyCodingStandard configurations for Contao

<p>
<a href="https://github.com/contao/easy-coding-standard/actions"><img src="https://github.com/contao/easy-coding-standard/actions/workflows/ci.yml/badge.svg?branch=main" alt></a>
<a href="https://packagist.org/packages/contao/easy-coding-standard"><img src="https://img.shields.io/packagist/v/contao/easy-coding-standard.svg" alt></a>
<a href="https://packagist.org/packages/contao/easy-coding-standard"><img src="https://img.shields.io/packagist/dt/contao/easy-coding-standard.svg" alt></a>
</p>

This package includes the [EasyCodingStandard][1] configuration for [Contao][2].

## Installation

Add the package to your Contao installation via Composer:

```bash
composer require contao/easy-coding-standard --dev
```

## Usage

Create a file named `ecs.php` in the root directory of your project.

```php
<?php

declare(strict_types=1);

use Contao\EasyCodingStandard\Set\SetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([SetList::CONTAO])
    // Adjust the configuration according to your needs.
;
```

Then run the script like this:

```bash
vendor/bin/ecs check
```

## What's inside?

The package contains the following custom fixers:

| Class | Description |
| --- | --- |
| [`AssertEqualsFixer`](src/Fixer/AssertEqualsFixer.php) | Replaces `asserEquals()` with `assertSame()` in unit tests unless the method is used to compare two objects. |
| [`CaseCommentIndentationFixer`](src/Fixer/CaseCommentIndentationFixer.php) | Fixes the comment indentation before a `case` statement. |
| [`ChainedMethodBlockFixer`](src/Fixer/ChainedMethodBlockFixer.php) | Adds an empty line after a block of chained method calls. |
| [`CommentLengthFixer`](src/Fixer/CommentLengthFixer.php) | Adjusts the length of comments regardless of their indentation so that each line is about 80 characters long. |
| [`ExpectsWithCallbackFixer`](src/Fixer/ExpectsWithCallbackFixer.php) | Adjusts the indentation of `$this->callback()` calls inside the `with()` method of a unit test. |
| [`FindByPkFixer`](src/Fixer/FindByPkFixer.php) | Replaces `findByPk()` calls with `findById()`. |
| [`FunctionCallWithMultilineArrayFixer`](src/Fixer/FunctionCallWithMultilineArrayFixer.php) | Fixes the indentation of function calls with multi-line array arguments. |
| [`InlinePhpdocCommentFixer`](src/Fixer/InlinePhpdocCommentFixer.php) | Ensures that inline phpDoc comments are not converted to regular comments. |
| [`IsArrayNotEmptyFixer`](src/Fixer/IsArrayNotEmptyFixer.php) | Fixes the order of `isset()` and `empty()` calls in conjunction with `is_array()` checks. |
| [`MockMethodChainingIndentationFixer`](src/Fixer/MockMethodChainingIndentationFixer.php) | Fixes the indentation of chained mock methods. |
| [`MultiLineIfIndentationFixer`](src/Fixer/MultiLineIfIndentationFixer.php) | Fixes the indentation of multi-line if statements. |
| [`MultiLineLambdaFunctionArgumentsFixer`](src/Fixer/MultiLineLambdaFunctionArgumentsFixer.php) | Fixes the indentation of multi-line lambda function arguments. |
| [`NoExpectsThisAnyFixer`](src/Fixer/NoExpectsThisAnyFixer.php) | Removes the explicit `any()` assertion in unit tests. |
| [`NoLineBreakBetweenMethodArgumentsFixer`](src/Fixer/NoLineBreakBetweenMethodArgumentsFixer.php) | Fixes the indentation of method declarations. |
| [`NoSemicolonAfterShortEchoTagFixer`](src/Fixer/NoSemicolonAfterShortEchoTagFixer.php) | Removes the semicolon after short echo tag instructions. |
| [`SingleLineConfigureCommandFixer`](src/Fixer/SingleLineConfigureCommandFixer.php) | Fixes the indentation of Symfony command arguments and options. |
| [`TypeHintOrderFixer`](src/Fixer/TypeHintOrderFixer.php) | Fixes the type hint order in method declarations. |

The package contains the following custom sniffs:

| Class | Description |
| --- | --- |
| [`ContaoFrameworkClassAliasSniff`](src/Sniffs/ContaoFrameworkClassAliasSniff.php) | Prevents using aliased Contao classes instead of their originals. |
| [`SetDefinitionCommandSniff`](src/Sniffs/SetDefinitionCommandSniff.php) | Prevents using the `setDefinition()` method in Symfony commands. |
| [`UseSprintfInExceptionsSniff`](src/Sniffs/UseSprintfInExceptionsSniff.php) | Prevents using string interpolation in exception messages. |

## License

Contao is licensed under the terms of the LGPLv3.

## Getting support

Visit the [support page][3] to learn about the available support options.

[1]: https://github.com/Symplify/EasyCodingStandard
[2]: https://contao.org
[3]: https://to.contao.org/support
