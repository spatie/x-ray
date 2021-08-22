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
