
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Quickly scan source code for calls to Ray

<p align="center">
    <img src="https://static.permafrost.dev/images/x-ray/x-ray-logo.png" alt="x-ray logo" height="200" style="display:block">
    <br><br>
    <!--img src="https://img.shields.io/packagist/v/spatie/x-ray.svg" alt="Packagist Version"-->  
    <img src="https://img.shields.io/github/v/release/spatie/x-ray.svg?sort=semver&logo=github" alt="Package Version">
    <img src="https://img.shields.io/github/license/spatie/x-ray.svg?logo=opensourceinitiative" alt="license">
    <img src="https://github.com/spatie/x-ray/actions/workflows/run-tests.yml/badge.svg?branch=main" alt="Test Run Status">
    <img src="https://img.shields.io/packagist/dt/spatie/x-ray.svg?style=flat-square" alt="Downloads" />
</p>

This package can quickly scan source code for calls to `ray()`, `rd()`, `Ray::*`, and `->ray()` helper methods from the [spatie/ray](https://github.com/spatie/ray) and [spatie/laravel-ray](https://github.com/spatie/laravel-ray) packages.

The primary use case is when calls to `ray()` cannot be left in source code before deploying, even if ray is disabled.  This package does NOT remove the calls, it simply displays their locations so they can be removed manually.

The exit code of the `x-ray` command is zero if no ray calls are found, and non-zero if calls are found.  This allows the package to be used in an automated environment such as Github Workflows.

Visit [myray.app](https://myray.app) for information on downloading the Ray debugging app.

## Installation

```bash
composer require spatie/x-ray --dev
```

## Usage

Specify one or more valid path names and/or filenames to scan:

```bash
./vendor/bin/x-ray ./app/Actions/MyAction.php ./app/Models/*.php ./tests --snippets
```

Display a summary table of the located calls within `./src` and `./tests` while also ignoring some files:

```bash
./vendor/bin/x-ray \
  --summary \
  --ignore src/MyClass.php \
  --ignore 'test/fixtures/*.php' \
  ./src ./tests
```

Display each filename & pass/fail status, along with compact results:

```bash
./vendor/bin/x-ray ./app --compact --verbose
```

## Available Options

| Flag | Description
|---|---|
|`--compact` or `-c` | Minimal output.  Display each result on a single line. |
|`--github` or `-g` | GitHub Annotation output.  Use `error` command to create annotation. Useful when you are running x-ray within GitHub Actions. |
|`--ignore` or `-i` | Ignore a file or path, can be specified multiple times. Accepts glob patterns. |
|`--no-progress` or `-P` | Don't display the progress bar while scanning files |
|`--snippets` or `-S` | Display code snippets from located calls |
|`--summary` or `-s` | Display a summary of the files/calls discovered |
|`--verbose` or `-v` | Display each filename and pass/fail status while scanning. Implies `--no-progress`. |

## Configuration files

Create a file named `x-ray.yml` or `x-ray.yml.dist` in the root directory of your project to configure the package.

If the configuration file exists, it must have the root sections `functions` and `paths`.

Each section may have an `ignore` or `include` item, both, or neither.  Each item contains an array of strings, and includes override ignores if an entry exists in both.  Add a `*` item to ignore or include all matches _(make sure to quote the asterisk)_.  

By default, `functions.include` matches the `ray` and `rd` functions.

```yaml
functions:
  include:
    - '*'

paths:
  include:
    - 'tests/fixtures/*'
  ignore:
    - tests
    - 'SettingsTest.php'
```

## Automation

`x-ray` was designed to be used not only as a manual utility, but in conjunction with automation tools.  

## Github workflows

You may use `ray-scan` within a github workflow to help ensure you don't commit any calls to `ray()`.

The following example workflow runs unit tests with PHPUnit and then runs `ray-scan`:

```yaml
name: run-tests

on: 
  push:
    branches:
      - main
  pull_request:

jobs:
  test:
    runs-on: ${{ matrix.os }}
    strategy:
      fail-fast: true
      matrix:
        os: [ubuntu-latest]
        php: [8.0, 7.4, 7.3]

    name: P${{ matrix.php }} - ${{ matrix.os }}

    steps:
      - name: Checkout code
        uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv, imagick, fileinfo
          coverage: pcov

      - name: Setup problem matchers
        run: |
          echo "::add-matcher::${{ runner.tool_cache }}/php.json"
          echo "::add-matcher::${{ runner.tool_cache }}/phpunit.json"

      - name: Install dependencies
        run: composer update --prefer-stable --prefer-dist --no-interaction

      - name: Execute tests
        run: ./vendor/bin/phpunit
        
      - name: Check for ray calls
        run: ./vendor/bin/x-ray . --compact
```

## Git hooks

In some cases you may want to use a git `pre-commit` hook to avoid commiting any `ray()` calls:

```bash
#!/bin/sh

echo "Checking for ray() calls...\n"

x-ray -s .
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
        "pre-commit": "lint-staged && .x-ray -s ."
    }
},
....
```


## Screenshots

Various screenshots can be viewed in [the docs](docs/screenshots.md).

## Testing

```bash
./vendor/bin/phpunit
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Patrick Organ](https://github.com/patinthehat)
- [Alex Vanderbist](https://github.com/AlexVanderbist)
- [Tom Witkowski](https://github.com/Gummibeer)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
