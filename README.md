<p align="center"><img src="art/social-card.png" alt="Social card of Filament Content Builder"></p>

# Filament Content Builder

[![Tests](https://github.com/VanOns/filament-content-builder/actions/workflows/run-tests.yml/badge.svg)](https://github.com/VanOns/filament-content-builder/actions/workflows/run-tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/van-ons/filament-content-builder.svg)](https://packagist.org/packages/van-ons/filament-content-builder)
[![License](https://img.shields.io/packagist/l/van-ons/filament-content-builder.svg)](LICENSE.md)

A content builder field for Filament with a set of basic content blocks. Full control over which blocks to use, with support for creating your own.

## Quick start

> For Filament version compatibility, see [Compatibility](docs/compatibility.md).

### Installation

Start by installing the package via Composer:

```bash
composer require van-ons/filament-content-builder
```

### Usage

Add the `ContentBlocksRenderer` field to your Filament resource:

```php
use VanOns\FilamentContentBuilder\Fields\ContentBlocksRenderer;

ContentBlocksRenderer::make('content')
    ->label(__('Content'))
    ->required(),
```

Then render the blocks in your Blade view:

```blade
<x-filament-content-builder::block-renderer :blocks="$post->content" />
```

## Documentation

Please see the [documentation](docs) for detailed information about installation and usage.

## Contributing

Please see [Contributing](CONTRIBUTING.md) for more information about how you can contribute.

## Testing

```bash
composer test
```

## Changelog

Please see [Changelog](CHANGELOG.md) for more information about what has changed recently.

## Upgrading

Please see [Upgrading](UPGRADING.md) for more information about how to upgrade.

## Security

Please see [Security](SECURITY.md) for more information about how we deal with security.

## Credits

We would like to thank the following contributors for their contributions to this project:

- [All contributors](../../contributors)

## License

The scripts and documentation in this project are released under the [MIT License](LICENSE.md).

---

<p align="center"><a href="https://van-ons.nl/" target="_blank"><img src="https://opensource.van-ons.nl/files/cow.png" width="50" alt="Logo of Van Ons"></a></p>
