# Copilot Instructions for SPIN Framework

Quick, actionable guidance to help an AI coding agent be productive in this repository.

## Big picture
## Big picture
- **Purpose:** a lightweight PHP 8+ web framework library. This repository contains the framework code (PSR-4 namespace `Spin\\` -> `src/`).
- **Scope:** focus on building and maintaining framework primitives: configuration, routing, middleware, controllers, cache, and connection managers. Example applications are out-of-repo and not covered here.

## Key components & where to look
- **Application entry / wiring:** `spin-skeleton/src/public/bootstrap.php` (app bootstrap for skeleton), `spin-framework/src/Application.php` (framework Application class).
 - **Application entry / wiring:** `src/Application.php` (framework Application class). Inspect this to understand service registration and bootstrap sequence.
- **Core services:** `spin-framework/src/Core/` contains `Config`, `ConnectionManager`, `CacheManager`, `Controller` base classes and other central components.
- **Routing & middleware:** routing is JSON-based (see `doc/Routing.md`) — route files define `common`/`groups`/`routes`, with middleware listed as class names. Middleware classes extend `Spin\Core\Middleware` and implement `initialize(array $args): bool` and `handle(array $args): bool`.
 - **Controllers:** controllers extend framework base controllers under `src/Core/`. Methods are named per HTTP method (e.g., `handleGET`, `handlePOST`). Controllers typically return `response()` or `responseJson()` helper outputs.
- **Configuration:** JSON config files use `${env:VAR}` macro support; see `doc/Configuration.md`.

## Project-specific conventions
- **PSR-4 autoloading:** namespace `Spin\` -> `src/` (see `composer.json`). Do not change class locations without updating autoload mapping.
- **JSON-first routing:** routes are defined in JSON, not code. When modifying routes, update the JSON files used by the app (skeleton uses `src/app/Config` route files).
 - **JSON-first routing:** routes are defined in JSON and parsed by the framework runtime (see `doc/Routing.md`). Avoid embedding route lists in core library code.
- **Middleware lifecycle:** implement `initialize()` for setup (reads config via `config()` helper) and `handle()` for per-request checks; returning `false` short-circuits the request.
- **Helpers & globals:** the codebase uses global helpers like `config()`, `response()`, `responseJson()`, and `getRequest()`. Use them instead of re-implementing request/response plumbing.
- **Strict types:** files typically declare `declare(strict_types=1);` — preserve this and prefer explicit typed signatures.

## Developer workflows & commands
## Developer workflows & commands
- **Install dependencies:** `composer install` in repository root to install framework development dependencies.
- **Run unit tests (Windows):** execute the included wrapper: `\\.\\phpunit.cmd` from the repository root. On Linux/macOS use `./vendor/bin/phpunit`.
- **Run tests with coverage:** `./vendor/bin/phpunit --coverage-html coverage/` (requires Xdebug or PCOV configured).
- **Static checks / coding style:** there is no centralized linter configuration in the repo; follow existing code style in `src/` (declare strict types, type hints).

## Important integration points and dependencies
- **HTTP factories / PSR:** uses `guzzlehttp/psr7` and PSR interfaces (`psr/http-message`, `psr/http-factory`). When creating HTTP messages use the project's factories.
- **Logging:** Monolog is the default; integrations expect PSR-3 compliance.
- **Caching:** multiple adapters are supported (`apcu`, `predis`). See `src/Cache/Adapters` and `doc/Cache.md` before adding new cache backends.
- **DB connections:** PDO-backed via `ConnectionManager`; configuration uses JSON — update `doc/Databases.md` for connection parameters.

## Patterns to emulate in code changes
- Use existing base classes in `src/Core/` (Controller, Middleware, CacheManager) rather than duplicating logic.
- Keep public API stable: this repo is a framework — avoid changing public class signatures unless necessary and document breaking changes in `changelog.md`.
- Tests live under `tests/` and follow the project's structure (see `tests/bootstrap.php`). Add unit tests next to the feature you change.

## Files to reference when editing or extending
- `composer.json` — dependency and autoload rules
- `readme.md` and `doc/*.md` — architecture and routing/middleware patterns
- `src/Application.php`, `src/Core/`, `src/Cache/` — core behavior to follow
 - `src/Application.php`, `src/Core/`, `src/Cache/` — core behavior to follow
 - `tests/` and `tests/bootstrap.php` — unit test examples that show expected public APIs and test patterns

## What not to change without discussion
- Global helper names and behavior (`config()`, `response()`, `getRequest()`), and JSON route format. These are relied upon by the skeleton and likely consumers.
- PSR-4 namespace mapping in `composer.json`.
 - **Global helper names and behavior:** `config()`, `response()`, `getRequest()`, and `responseJson()` — changing these affects many consumers.
 - **PSR-4 namespace mapping:** do not change PSR-4 namespace mapping in `composer.json` without coordination.

If anything in this summary is unclear or you'd like me to expand a section (examples for middleware, a list of route JSON fields, or a generated quick-reference), tell me which part to expand.
