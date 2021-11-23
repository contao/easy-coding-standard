# EasyCodingStandard configurations for Contao

[![](https://img.shields.io/github/workflow/status/contao/easy-coding-standard/CI/master.svg)](https://github.com/contao/easy-coding-standard/actions)
[![](https://img.shields.io/packagist/v/contao/easy-coding-standard.svg?style=flat-square)](https://packagist.org/packages/contao/easy-coding-standard)
[![](https://img.shields.io/packagist/dt/contao/easy-coding-standard.svg?style=flat-square)](https://packagist.org/packages/contao/easy-coding-standard)

This package includes the [EasyCodingStandard][1] configurations for [Contao][2].

## Installation

You can install the package with Composer:

```
composer require contao/easy-coding-standard
```

## Usage

Create a file named `ecs.php` in the root directory of your project.

```php
<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__.'/vendor/contao/easy-coding-standard/config/contao.php');

    // Adjust the configuration according to your needs.
};
```

Then run the script like this:

```
vendor/bin/ecs check src tests
```

## License

Contao is licensed under the terms of the LGPLv3.

## Getting support

Visit the [support page][3] to learn about the available support options.

[1]: https://github.com/Symplify/EasyCodingStandard
[2]: https://contao.org
[3]: https://contao.org/en/support.html
