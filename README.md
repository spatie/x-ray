# ray-scan

[![Latest Version on Packagist](https://img.shields.io/packagist/v/permafrost-dev/ray-scan.svg?style=flat-square)](https://packagist.org/packages/permafrost-dev/ray-scan)
[![GitHub Tests Action Status](https://github.com/permafrost-dev/ray-scan/actions/workflows/run-tests.yml/badge.svg)](https://github.com/permafrost-dev/ray-scan/actions/workflows/run-tests.yml)
---

Scan source code for calls to `ray()` and `rd()` from the [spatie/ray](https://github.com/spatie/ray) package.

## Installation

```bash
composer require permafrost-dev/ray-scan
```

## Usage

```bash
./vendor/bin/ray-scan /some/project/path/src
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Patrick Organ](https://github.com/patinthehat)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
