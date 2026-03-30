
# Changelog
SPIN Framework Changelog

## 0.0.40
- **Feature:** Add domain exception classes: `CacheException`, `ConfigException`, `DatabaseException`, `MiddlewareException` — all extending `SpinException`
- **Feature:** Wire `CacheException` into cache adapters (APCu, Redis), `ConfigException` into `Config`, `DatabaseException` into `PdoConnection` and `ConnectionManager`
- **Fix:** `ConnectionManager` password-sanitization now throws `DatabaseException` without `$previous` on tainted traces, preventing credential leakage

## 0.0.39
- **Feature:** `.env` file auto-loading — `DotEnv::load()` reads `.env` from the project root at startup, populating environment variables before config macro expansion and `env()` calls; real process env vars always take precedence
- **Fix:** `${env:VAR:default}` inline default syntax in config macros now works as documented; missing variables now resolve to the specified default instead of silently dropping to empty string

## 0.0.38
- **Fix:** Removed incorrect route-level middleware documentation from recipes and user guide; middleware applies at common/group level only
- **Fix:** CORS-Handling, Authentication, and Rate-Limiting recipe docs updated with correct group-scoped middleware patterns
- **Feature:** Controller short-name resolution — bare class names in route `handler` fields are resolved via `App\Controllers\{Name}` fallback
- **Feature:** Middleware short-name resolution — bare class names in `before`/`after` arrays are resolved via `App\Middlewares\{Name}` fallback

## 0.0.37
- **Documentation:** Comprehensive developer documentation with Getting Started guides, Best Practices, Recipes, and Contributor Guide sections
- **Documentation:** AI instructions for Claude Code, GitHub Copilot, and generic LLMs in dedicated instruction files
- **Documentation:** Reorganized doc/ structure with navigation hub (doc/README.md) guiding users to appropriate sections
- **Documentation:** Complete API reference documenting core framework classes, methods, and helper functions
- **Documentation:** Updated CLAUDE.md and README.md with references to comprehensive documentation

## 0.0.36
- **Security:** Upgraded firebase/php-jwt from v6 to v7 (resolves PKSA-y2cr-5h3j-g3ys vulnerability)
- **Bug Fix:** APCu cache adapter now properly handles DateInterval objects in set() method
- **Bug Fix:** JWT test key length updated to meet firebase/php-jwt v7 stricter validation (32+ bytes for HS256)
- **CI/CD:** Enhanced GitHub Actions pipeline with Redis, MySQL, and PostgreSQL sidecar services
- **CI/CD:** Added memory overcommit configuration for reliable Redis startup in CI environments
- **Testing:** Added database and cache adapter configurations to test suite (config-unittest.json)
- **Testing:** Enabled autodiscovery of external services (Redis, MySQL, PostgreSQL) for conditional test execution
- Code cleanup and refactoring

## 0.0.35
- Unittests updated to support PHP 8.4 and PHPUnit 12
- Docblocks refactored in many places
- Config automatically replaces ${env:VAR} environment macros on loading
- Hook related things removed from codebase

## 0.0.34
- Bugfix on PDO not correctly parsing default options
- Add Redit and cache adapters unit tests with CI sidecar container
- Add MySQL and PDO unit tests with CI sidecar container

## 0.0.33
- More League/Container deprecated usage fix

## 0.0.32
- Update League/Container usage preventing Spin from booting up

## 0.0.31
- Better compatibility with PHP 8.1
- Fix MySQL PDO driver compatibility (Removal of overridden connect base method)

## 0.0.30
- Remove deprecated error constant E_STRICT usage
- Remove AbstractBaseDaos

## 0.0.29
- Extracted `RequestIdClass` from `Application.php` into its own file under `src/Classes/RequestIdClass.php`.
- Updated all usages to reference the new class location and namespace.
- Added comprehensive class and method docblocks to `RequestIdClass` for improved code documentation and IDE support.
- Fixed Redis adapter test to pass connection options in the correct structure (`options` key).
- Fixed Redis adapter to throw clear exception if connection options are missing.
- Updated container test to use correct `RequestIdClass` namespace and string casting for assertion.

## 0.0.28
- Support for PHP 8.4

## 0.0.26
- Updated composer.json with newer versions of packages

## 0.0.25
- New options for logging. "max_buffered_lines" and "flush_overflow_to_disk"

## 0.0.24
- Composer packages updated to newer versions
- Redis adapter fixes
- Tests updated
- Docblock corrections
- VERSION file added

## 0.0.23
- Added extended cipher methods

## 0.0.22
- Added "PHP Fatal Error" catching, making framework return a 500 Error to the caller with the last error message

## 0.0.21
- Variable definitions in classes with `/** @var` docblocks in most places

## 0.0.20
- Guzzle v2.0.0 stream support via `Utils::streamFor()`

## 0.0.19
- Started using Ramsey\UUID for UUID generation
- Added UUID v6 generation as default in UUID::generate() method

## 0.0.18
- app()->setCookie() internally uses setCookie() with array to set HTTPOnly=true and 'SameSite'=>'Strict' for cookies by default

## 0.0.17
- container() now uses internal globalVars instead when get/set variables

## 0.0.16
- Added `copy` to UploaedFiles class

## 0.0.15
- Storing/Getter for initial memory usage
- Determine Mime-Type for file (if not set)

## 0.0.14
- Support for empty `path` in routeGroups

## 0.0.13
- PDO param binding examines the type of value passed, and uses correct PDO::PARAM_* for it

## 0.0.12
- Composer v2 compatibility, class names corrected

## 0.0.11
- responseXML() fixed
- Package.json version corrected
- PHP v7.4.x compatibility verified
- responseFile() has new param for removing file after sending

## 0.0.10
- UploadedFile and UploadedFilesmanager fixes

## 0.0.9
- Added `getSharedStoragePath()` (docker persitent storage compatibility)
- Root namespaces for `Helpers.php`
- Added error-checking for some variables on application startup
- License date updated
- Unittests updated to work with PHPUnit v8
- Technical documentation pages
- Monolog v2+ package in composer
- Leauge/Container v3+
-

## 0.0.8
- Added \ for all root namespace functions (performance enhancement)
- Code cleanup
- Unittests dir /tests rearranged
- Apcu basic unittest added

## 0.0.7
- Added postParams() method to helpers

## 0.0.6
- No recorded changes

## 0.0.5
- No recorded changes

## 0.0.4
- Changed the Database Connection system. New AbstractConnection class.

## 0.0.x
- Initial code
