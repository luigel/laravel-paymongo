# Paymongo for Laravel

![Run tests](https://github.com/luigel/laravel-paymongo/workflows/Run%20tests/badge.svg)
[![Quality Score](https://img.shields.io/scrutinizer/g/luigel/laravel-paymongo.svg?style=flat-square)](https://scrutinizer-ci.com/g/luigel/laravel-paymongo)
[![Latest Stable Version](https://poser.pugx.org/luigel/laravel-paymongo/v)](//packagist.org/packages/luigel/laravel-paymongo)
[![Total Downloads](https://poser.pugx.org/luigel/laravel-paymongo/downloads)](//packagist.org/packages/luigel/laravel-paymongo)
[![Monthly Downloads](https://poser.pugx.org/luigel/laravel-paymongo/d/monthly)](//packagist.org/packages/luigel/laravel-paymongo)
[![Daily Downloads](https://poser.pugx.org/luigel/laravel-paymongo/d/daily)](//packagist.org/packages/luigel/laravel-paymongo)
[![License](https://poser.pugx.org/luigel/laravel-paymongo/license)](//packagist.org/packages/luigel/laravel-paymongo)

A PHP Library for [Paymongo](https://paymongo.com).

This package is not affiliated with [Paymongo](https://paymongo.com). The package requires PHP 7.2+

## Documentation

https://paymongo.rigelkentcarbonel.com

### **Todo**

- [x] Add unit test for the `BaseModel`.
- [ ] Fix the magic method when accessing a nested data with `underscore` ("\_").
- [x] Add artisan commands for adding, enabling, and disabling webhooks.
- [x] Fix the test case for the `PaymongoValidateSignature` middleware.
- [x] Transfer from travis to github actions.
- [x] Refactor test cases into Pest.

## Laravel Version Compatibility

Laravel  | Package
:---------|:----------
5.8.x    | 1.x
6.x.x    | 1.x
7.x.x    | 1.x
8.x.x    | 2.x

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email rigel20.kent@gmail.com instead of using the issue tracker.

## Credits

-   [Rigel Kent Carbonel](https://github.com/luigel)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

Made with :heart: by Rigel Kent Carbonel
