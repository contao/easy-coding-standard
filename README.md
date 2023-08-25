# EasyCodingStandard configurations for Contao

<p>
<a href="https://github.com/contao/easy-coding-standard/actions"><img src="https://github.com/contao/easy-coding-standard/actions/workflows/ci.yml/badge.svg?branch=main" alt></a>
<a href="https://packagist.org/packages/contao/easy-coding-standard"><img src="https://img.shields.io/packagist/v/contao/easy-coding-standard.svg" alt></a>
<a href="https://packagist.org/packages/contao/easy-coding-standard"><img src="https://img.shields.io/packagist/dt/contao/easy-coding-standard.svg" alt></a>
</p>

This package includes the [EasyCodingStandard][1] configuration for [Contao][2].

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

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([__DIR__.'/vendor/contao/easy-coding-standard/config/contao.php']);

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
