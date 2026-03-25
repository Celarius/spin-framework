# HISTORY.md — SPIN Framework Project History

Narrative history and architectural evolution of the SPIN Framework. For a raw list of changes per version see [CHANGELOG.md](CHANGELOG.md).

## Origins

SPIN began as a minimal PHP framework to support REST API and web application development with as little overhead as possible. The project is authored and maintained by Kim Sandell (Celarius) and published on Packagist as `celarius/spin-framework`.

From the very first commits the framework committed to a few non-negotiable principles:
- **Lightweight** — no bloat, only what is needed.
- **PSR-first** — adopt PSR interfaces everywhere instead of custom contracts.
- **JSON-driven config and routing** — no PHP DSL for routes or config, just JSON files.

## Early Development (0.0.1 – 0.0.9)

The initial versions established the core skeleton:

- `Application.php` as the single orchestrator for bootstrap, routing, and middleware dispatch.
- PDO-based database connection pooling via `ConnectionManager`.
- JSON configuration with environment-specific files (`config-dev.json`, `config-prod.json`).
- JSON route files with `common`/`groups`/`routes` structure.
- PSR-3 logging via Monolog.
- PHPUnit test suite under `tests/`.
- Technical documentation pages in `doc/`.
- Docker-friendly shared storage path helper (`getSharedStoragePath()`).
- Monolog v2+ and League/Container v3+ adopted.

## Stabilisation (0.0.10 – 0.0.19)

Focus shifted from structure to correctness and ergonomics:

- `UploadedFile` / `UploadedFilesManager` bug fixes and copy support.
- Composer v2 compatibility.
- `responseXML()` fix; `responseFile()` gained option to delete after send.
- PDO param binding made type-aware (`PDO::PARAM_INT`, `PDO::PARAM_STR`, etc.).
- Support for empty `path` in route groups.
- Initial memory usage stored and exposed for diagnostics.
- UUID v6 generation via `ramsey/uuid`.
- `postParams()` helper added.
- PHP 7.4.x compatibility verified (later dropped in favour of PHP 8+).

## Modern PHP & Feature Expansion (0.0.20 – 0.0.27)

- Guzzle v2 stream support via `Utils::streamFor()`.
- Comprehensive `@var` docblocks added across all classes.
- PHP Fatal Error catching — framework returns a proper 500 response instead of a blank page.
- Extended Cipher helper methods.
- New logging options: `max_buffered_lines` and `flush_overflow_to_disk` for high-throughput scenarios.
- Composer dependency updates across the board.
- Redis adapter fixes and test improvements.
- `VERSION` file introduced.
- `container()` helper changed to use internal `globalVars` for get/set.
- Cookie defaults hardened: `HTTPOnly=true`, `SameSite=Strict`.

## PHP 8+ Modernisation (0.0.28 – 0.0.35)

The project fully committed to PHP 8+ and began cleaning up legacy patterns:

- **0.0.28** — PHP 8.4 support declared.
- **0.0.29** — `RequestIdClass` extracted from `Application.php` into its own file (`src/Classes/RequestIdClass.php`) for better separation of concerns; comprehensive docblocks added; Redis adapter hardened with explicit exception on missing config.
- **0.0.30** — Deprecated `E_STRICT` error constant removed; `AbstractBaseDaos` removed.
- **0.0.31** — PHP 8.1 compatibility improvements; MySQL PDO driver cleaned up (removed overridden `connect` base method).
- **0.0.32 / 0.0.33** — League/Container v5 migration; breaking API usage updated to prevent boot failures.
- **0.0.34** — PDO default options parsing fixed; Redis and MySQL CI sidecar container tests added.
- **0.0.35** — PHPUnit 12 and PHP 8.4 unit test support; docblock refactor across the codebase; `Config` now auto-replaces `${env:VAR}` macros at load time (not lazily); hook-related code removed entirely.

## Current State (as of 2026-03-15)

- **Version:** 0.0.35
- **PHP:** 8.0 – 8.4 supported
- **PHPUnit:** 10.5 / 11.x / 12.x
- **Status:** Active development, pre-1.0

The framework is used via the [spin-skeleton](https://github.com/Celarius/spin-skeleton) companion project which provides the application scaffolding, public bootstrap, and sample config/route files.

## Architectural Highlights

| Concern | Approach |
|---------|---------|
| Routing | FastRoute + JSON route files |
| HTTP messages | Guzzle PSR-7 + PSR-17 factories |
| Middleware | Three-level pipeline (global → group → controller) |
| DI container | League/Container (PSR-11) |
| Configuration | JSON files + `${env:VAR}` macro expansion |
| Logging | Monolog (PSR-3) |
| Caching | Pluggable adapters: APCu, Redis, File (PSR-16) |
| Database | PDO pool: MySQL, PostgreSQL, SQLite, CockroachDB, Firebird, ODBC |
| Security | `firebase/php-jwt`, custom Cipher/Hash/EWT helpers |
| Testing | PHPUnit + CI sidecar containers for Redis/MySQL |
