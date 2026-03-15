# CLAUDE.md — SPIN Framework

Guidance for Claude Code when working in this repository.

## Project Overview

**SPIN Framework** is a lightweight PHP 8+ web framework for building web apps and REST APIs.
- **Package:** `celarius/spin-framework`
- **Namespace:** `Spin\` → `src/`
- **Author:** Kim Sandell (sandell@celarius.com)
- **License:** MIT
- **Current version:** 0.0.35 (pre-1.0, actively developed)

## Repository Layout

```
src/
  Application.php         # Main orchestrator — request/response lifecycle
  Core/                   # Config, Controller, Middleware, RouteGroup, Logger,
                          #   CacheManager, ConnectionManager, Route, UploadedFile(s)
  Cache/Adapters/         # APCu, Redis, File-based cache adapters
  Database/Drivers/Pdo/   # MySQL, PostgreSQL, SQLite, CockroachDB, Firebird, ODBC
  Helpers/                # Cipher, JWT, EWT, UUID, ArrayToXml, Hash
  Factories/Http/         # PSR-17 HTTP message factories
  Classes/                # RequestIdClass
  Exceptions/             # Framework exception classes
tests/                    # PHPUnit test suite (mirrors src/ structure)
doc/                      # Markdown documentation per feature area
.github/copilot-instructions.md
```

## Key Architectural Decisions

- **JSON routing** — routes are never defined in PHP code. Route files use `common`/`groups`/`routes` keys. See [doc/Routing.md](doc/Routing.md).
- **JSON configuration** — `config-{env}.json` files with `${env:VAR}` macro expansion. See [doc/Configuration.md](doc/Configuration.md).
- **Middleware pipeline** — global-before → group-before → controller → group-after → global-after. Returning `false` from `handle()` short-circuits the chain.
- **PSR compliance** — PSR-3 (logging), PSR-7 (HTTP messages), PSR-11 (container), PSR-16 (cache), PSR-17 (HTTP factories).
- **Global helpers** — `app()`, `config()`, `getRequest()`, `getResponse()`, `response()`, `responseJson()`, `logger()`, `cache()`, `env()`. Use them instead of reimplementing plumbing.

## Extending the Framework

### Adding a Controller

Controllers extend `Spin\Core\Controller`. Override HTTP method handlers:

```php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class MyController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        return responseJson(['key' => 'value']);
    }
}
```

### Adding Middleware

Middleware extends `Spin\Core\Middleware` and implements two methods:

```php
public function initialize(array $args): bool  // setup, read config
public function handle(array $args): bool       // per-request logic; false = short-circuit
```

### Adding a Cache Adapter

Extend `Spin\Cache\Adapters\AbstractCacheAdapter` and register it in the cache config section.

### Adding a Database Driver

Extend the PDO base class under `src/Database/Drivers/Pdo/` and register the driver name in config.

## Commands

```bash
# Install dependencies
composer install

# Run tests (Windows)
.\phpunit.cmd

# Run tests (Linux/macOS)
./vendor/bin/phpunit

# Run with coverage (requires Xdebug or PCOV)
./vendor/bin/phpunit --coverage-html coverage/
```

## Coding Conventions

- Always declare `declare(strict_types=1);` at the top of every file.
- Use explicit typed method signatures (no mixed unless unavoidable).
- Follow PSR-4: class `Spin\Core\Foo` lives in `src/Core/Foo.php`.
- Add docblocks to all public methods and class-level `@var` annotations to properties.
- Tests live in `tests/` mirroring the `src/` structure; add tests alongside any feature change.

## What NOT to Change Without Discussion

- **Global helper names/signatures** — `config()`, `response()`, `responseJson()`, `getRequest()` are consumed by all downstream apps.
- **PSR-4 namespace mapping** in `composer.json` (`Spin\` → `src/`).
- **JSON route format** — breaking the schema breaks all consumer route files.
- **Public class APIs** in `src/Core/` — this is a library; downstream breakage is hard to detect.

Document any breaking changes in [CHANGELOG.md](CHANGELOG.md).

## Dependencies

| Package | Purpose |
|---------|---------|
| `nikic/fast-route` | URL routing |
| `guzzlehttp/guzzle` | PSR-7 HTTP messages |
| `monolog/monolog` | PSR-3 logging |
| `firebase/php-jwt` | JWT token support |
| `league/container` | PSR-11 DI container |
| `ramsey/uuid` | UUID generation |
| `predis/predis` | Redis client |
| `ext-apcu` *(optional)* | In-memory cache |

## Related Repository

The companion skeleton project lives at `c:\Data\Repos\Celarius\github\spin-skeleton` and demonstrates how an application uses this framework.

# Customization

## Commit messages

Never include "Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>" in commit messages.