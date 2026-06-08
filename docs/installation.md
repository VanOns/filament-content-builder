# Installation

Start by installing the package via Composer:

```bash
composer require van-ons/filament-content-builder
```

### Customizing the config

The config file defines which blocks are available in the content builder. Publish it with:

```bash
php artisan vendor:publish --tag=filament-content-builder-config
```

### Customizing the views

Publish the views for the default blocks and the block renderer component:

```bash
php artisan vendor:publish --tag=filament-content-builder-views
```

### Customizing the stubs

Stubs are used when generating new blocks. Publish them with:

```bash
php artisan vendor:publish --tag=filament-content-builder-stubs
```

### Customizing the language files

```bash
php artisan vendor:publish --tag=filament-content-builder-lang
```
