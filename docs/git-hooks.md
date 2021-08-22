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
        "pre-commit": "lint-staged && .ray-scan -s ."
    }
},
....
```
