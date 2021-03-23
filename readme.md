# Laravel Json Api Generator 0.1a

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

Based on jsonapi.org. Alpha version.

## Installation

Via Composer

``` bash
$ composer require imjonos/laravel-json-api-generator
```

## Usage

jsonApi:generate {table : Table name from DB} {--route=v1} {--force=0}

php artisan jsonApi:generate posts

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Eugeny Nosenko][link-author]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/nos/jsonapigenerator.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/nos/jsonapigenerator.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/imjonos/laravel-json-api-generator
[link-downloads]: https://packagist.org/packages/imjonos/laravel-json-api-generator
[link-author]: https://github.com/imjonos
[link-contributors]: ../../contributors
