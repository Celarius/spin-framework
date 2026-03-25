Run the PHPUnit test suite and report results.

On Windows run `.\phpunit.cmd` from the repo root.
On Linux/macOS run `./vendor/bin/phpunit`.

If $ARGUMENTS contains "coverage", add `--coverage-html coverage/` to the command (requires Xdebug or PCOV).

Report the number of tests passed, failed, and any errors with their file and line number.
