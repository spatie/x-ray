# ray-scan

<p align="center">
    <img src="https://static.permafrost.dev/images/ray-scan/ray-scan-logo.png" alt="ray-scan logo" height="200" style="block">
    <br><br>
    <!--img src="https://img.shields.io/packagist/v/permafrost-dev/ray-scan.svg" alt="Packagist Version"-->  
    <img src="https://img.shields.io/github/v/release/permafrost-dev/ray-scan.svg?sort=semver&logo=github" alt="Package Version">
    <img src="https://img.shields.io/github/license/permafrost-dev/ray-scan.svg?logo=opensourceinitiative" alt="license">
    <img src="https://github.com/permafrost-dev/ray-scan/actions/workflows/run-tests.yml/badge.svg?branch=main" alt="Test Run Status">
    <img src="https://codecov.io/gh/permafrost-dev/ray-scan/branch/main/graph/badge.svg?token=JPmlhRV95Y" alt="code coverage">
</p>

---

Testing instructions:  Clone the repo, then run:

```bash
composer install
./bin/ray-scan . -S
```

---

Scan source code for calls to `ray()`, `rd()` and `Ray::*` _(static calls)_ from the [spatie/ray](https://github.com/spatie/ray) and [spatie/laravel-ray](https://github.com/spatie/laravel-ray) _(and other)_ packages.

The primary use case for this package is when calls to `ray()` cannot be left in the source code before deploying, even if ray is disabled.  This package does NOT remove the function calls, it only displays their locations so they can be removed manually.

The exit code of the `ray-scan` command is zero if no ray calls are found, and non-zero if calls are found.  This allows the package to be used in an automated environment such as Github Workflows.

See [myray.app](https://myray.app) for information on downloading the Ray debugging app.

---

## Installation

```bash
composer require permafrost-dev/ray-scan --dev
```

## Usage

Specify one or more valid pathnames and/or filenames to scan:

```bash
./vendor/bin/ray-scan ./app/Actions/MyAction.php ./app/Models/*.php ./tests
./vendor/bin/ray-scan ./app --snippets

# display only a summary of the located calls
./vendor/bin/ray-scan ./src --summary

# display minimal scan results
./vendor/bin/ray-scan ./app --compact
./vendor/bin/ray-scan ./app --compact --no-progress
```

## Available Options

| Flag | Description
|---|---|
|`--no-progress` or `-P` | Don't display the progress bar while scanning files |
|`--snippets` or `-S` | Display code snippets from located calls |
|`--summary` or `-s` | Display a summary of the files/calls discovered |
|`--compact` or `-c` | Display each result on a single line, with minimal output | 

## Configuration files

Create a file named `ray-scan.php` in the root directory of your project to configure the package.

```php
<?php

return [
    'ignore' => [
        // don't flag these functions as errors    
        'functions' => [
            'rd',
        ],
    
        // don't scan these filenames or paths; 
        // glob patterns are supported.
        'paths' => [
            'test1.php',
            '*/tests/*',
            'app/Http/*'
        ],                
    ],
];
```

## Git hooks

In some cases you may want to use a git `pre-commit` hook to avoid commiting any `ray()` calls:

```bash
#!/bin/sh

echo "Checking for ray() calls...\n"

ray-scan -s .
rayScanExitCode=$?

printf '%*s\n' "${COLUMNS:-$(tput cols)}" '' | tr ' ' -

localPreCommitExitCode=0
if [ -e ./.git/hooks/pre-commit ]; then
    ./.git/hooks/pre-commit "$@"
    localPreCommitExitCode=$?
fi

exit $rayScanExitCode || $localPreCommitExitCode
```

You can also use `ray-scan` with husky in your `package.json` configuration:

```json
...
"husky": {
    "hooks": {
        "pre-commit": "lint-staged && ray-scan -s ."
    }
},
....
```

## Screenshots

<p>
    <img width="450" src="https://user-images.githubusercontent.com/5508707/126883494-57cffebc-6fb1-4dff-9e10-148770437fcc.png" alt="output">
    <img src="https://user-images.githubusercontent.com/5508707/127717212-6cd02efc-e148-4aca-80bd-070bd4649dc2.png" alt="compact output">
    <img width="450" height="180" src="https://user-images.githubusercontent.com/5508707/126883461-f56e74c9-f427-4f59-8ece-385b5d013b78.png" alt="">
    <img width="450" height="180" src="https://user-images.githubusercontent.com/5508707/126883434-3bc6fbad-73c1-4144-a587-cdf59c74b6ce.png" alt="">
    <img width="450" src="https://user-images.githubusercontent.com/5508707/126883470-1224ed2b-38ed-4772-9d8b-65f71539119d.png" alt="">
    <img width="450" src="https://user-images.githubusercontent.com/5508707/126909292-8a8fa93e-7a45-4c65-855f-30edb0092806.png" alt="">
</p>

## Testing

```bash
./vendor/bin/phpunit
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
