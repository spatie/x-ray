# ray-scan

<p align="center">
    <img src="https://static.permafrost.dev/images/ray-scan/ray-scan-logo.png" alt="ray-scan logo" height="200" style="block">
    <br><br>
    <img src="https://img.shields.io/packagist/v/permafrost-dev/ray-scan.svg" alt="Packagist Version">
    <img src="https://img.shields.io/github/license/permafrost-dev/ray-scan.svg" alt="license">
    <img src="https://github.com/permafrost-dev/ray-scan/actions/workflows/run-tests.yml/badge.svg" alt="Test Run Status">
</p>

---

Scan source code for calls to `ray()` and `rd()` from the [spatie/ray](https://github.com/spatie/ray) and [spatie/laravel-ray](https://github.com/spatie/laravel-ray) packages.  Visit [myray.app](https://myray.app) for information on downloading the Ray debugging app.

---

## Installation

```bash
composer require permafrost-dev/ray-scan
```

## Usage

Specify either a valid path name or a valid filename to scan:

```bash
./vendor/bin/ray-scan /some/project/path/src
./vendor/bin/ray-scan ./app
./vendor/bin/ray-scan ./app/Models/User.php
```

## Sample Output

![image](https://user-images.githubusercontent.com/5508707/123883510-27321180-d917-11eb-8734-f0e4fcbf4201.png)

![image](https://user-images.githubusercontent.com/5508707/123883450-0d90ca00-d917-11eb-89dc-ccb604a655b3.png)

![image](https://user-images.githubusercontent.com/5508707/123883749-990a5b00-d917-11eb-9020-6aa3d6053203.png)

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
