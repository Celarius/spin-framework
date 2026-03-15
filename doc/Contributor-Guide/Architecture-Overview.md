# Architecture Overview — SPIN Framework

This document describes the core architectural principles, components, and request lifecycle of the SPIN Framework.

## Core Design Principles

- **Lightweight** — Minimal dependencies, focused on essential features
- **PHP 8+ Native** — Full typed language features, strict types enforced
- **PSR Compliance** — Standards-based for interoperability
- **JSON-Driven** — Routes and configuration via JSON (never magic)
- **Extension-Friendly** — Clear interfaces for custom implementations
- **Zero-Magic** — Explicit over implicit; no hidden behaviors

## Component Architecture

### Application Core

**`Application.php`** orchestrates the entire request/response lifecycle:

```
[Request]
    ↓
[Application::run()]
    ↓
[Global Before Middleware]
    ↓
[Route Dispatcher]
    ↓
[Route-Group Before Middleware]
    ↓
[Route Middleware]
    ↓
[Controller Handler]
    ↓
[Route-Group After Middleware]
    ↓
[Global After Middleware]
    ↓
[Response] → [Client]
```

Each middleware can short-circuit by returning `false` from `handle()`.

### Request Lifecycle Detail

1. **Bootstrap** — `Application` loads configuration and initializes container
2. **Middleware Pipeline Setup** — Global, group, and route middleware registered
3. **Route Matching** — HTTP method and URI matched against routes using nikic/fast-route
4. **Middleware Execution** — Before middleware chain executes top-down
5. **Controller Dispatch** — Matched controller's `handleGET()`, `handlePOST()`, etc. invoked
6. **Response Processing** — After middleware chain executes bottom-up
7. **Response Emission** — PSR-7 response sent to client

### Container (Dependency Injection)

Uses **league/container** (PSR-11 compliant):

```php
// Registered in Application::__construct()
$this->container->add(CacheManager::class)
    ->setShared(true);

// Resolved anywhere in framework
$cache = app()->getContainer()->get(CacheManager::class);
```

**Benefits:**
- Decouples dependencies
- Enables testing with mock implementations
- Allows dynamic service replacement

### Configuration Management

**`Core\Config`** manages JSON-based configuration:

```php
// Configuration loaded from config-{env}.json
$db_host = config('database.pdo.mysql.host');

// Macro expansion: ${env:DB_HOST} replaced with $_ENV['DB_HOST']
// Nested array/object access supported
```

Key features:
- Environment-specific configuration files
- Macro expansion for secrets
- Type-safe value access
- Caching for performance

## Core Components

### Controllers

**`Core\Controller`** — Base class for request handlers:

```php
abstract class Controller
{
    public function handleGET(array $args): ResponseInterface
    public function handlePOST(array $args): ResponseInterface
    public function handlePUT(array $args): ResponseInterface
    public function handleDELETE(array $args): ResponseInterface
    // ... other HTTP methods
}
```

- Route parameters passed as `$args` array
- Must return `ResponseInterface` (PSR-7)
- Access request via `getRequest()`
- Access response helper via `response()`

### Middleware

**`Core\Middleware`** — Pipeline interceptors:

```php
abstract class Middleware
{
    // Setup phase: read config, initialize state
    public function initialize(array $args): bool

    // Per-request phase: validate, transform, short-circuit
    public function handle(array $args): bool
}
```

Execution order:
1. Global before middleware (in order)
2. Route-group before middleware (in order)
3. Route-specific middleware
4. Controller handler
5. Route-group after middleware (reverse order)
6. Global after middleware (reverse order)

### Routing

**Routes** defined in JSON (never code):

```json
{
  "routes": [
    {
      "path": "/api/users/:id",
      "methods": ["GET", "PUT", "DELETE"],
      "controller": "App\\Controllers\\UserController",
      "name": "user.detail"
    }
  ]
}
```

Features:
- Dynamic parameters: `:id` captured as `$args['id']`
- Named routes for reverse routing
- Route groups with shared prefixes/middleware
- PSR-7 compatible parameter handling

### Cache Manager

**`Core\CacheManager`** — PSR-16 compliant cache abstraction:

```php
$cache = cache();
$value = $cache->get('key', 'default');
$cache->set('key', $value, 3600); // TTL in seconds
```

Supported adapters:
- `APCu` — In-memory (production, single-server)
- `Redis` — Distributed cache
- `File` — Development/testing

Adapters implement `CacheInterface` and can be swapped via configuration.

### Database Connection Management

**`Core\ConnectionManager`** — Connection pooling and driver selection:

```php
$conn = app()->getConnectionManager()->connection('default');
$result = $conn->query('SELECT * FROM users WHERE id = ?', [$id]);
```

Supported drivers:
- MySQL/MariaDB (PDO)
- PostgreSQL (PDO)
- SQLite (PDO)
- CockroachDB (PostgreSQL protocol)
- Firebird
- ODBC

Driver instantiation via factory pattern, configuration-driven.

### Logging

**`Core\Logger`** — PSR-3 compliant logging using Monolog:

```php
logger()->info('User logged in', ['user_id' => 123]);
logger()->error('Database error', ['exception' => $e]);
```

Configuration drives:
- Log level thresholds
- Output channels (file, stream, Slack, etc.)
- Contextual metadata

## Global Helpers

Framework provides global functions for common operations:

```php
// Application and container access
app()                    // Application instance
app()->getContainer()    // PSR-11 container

// Configuration and environment
config('key.path')       // Get config value
env('VAR_NAME', 'default')  // Get environment variable

// HTTP request/response
getRequest()            // Current PSR-7 Request
getResponse()           // Current PSR-7 Response
response()              // Create Response
responseJson(['data' => 'value'])  // JSON response

// Other services
logger()                // PSR-3 Logger
cache()                 // PSR-16 Cache
```

These helpers are consumed by downstream applications — breaking their signatures requires major version bumps.

## Extension Points

Framework designed for extensibility:

### 1. Cache Adapters

Extend `Cache\Adapters\AbstractCacheAdapter`:

```php
class MyAdapter extends AbstractCacheAdapter
{
    public function has(string $key): bool { }
    public function get(string $key, $default = null) { }
    public function set(string $key, $value, $ttl = null): bool { }
    public function delete(string $key): bool { }
}
```

Register in `config-{env}.json`:

```json
{
  "cache": {
    "adapter": "App\\Cache\\MyAdapter",
    "options": {}
  }
}
```

### 2. Database Drivers

Extend `Database\Drivers\Pdo\AbstractPdoDriver`:

```php
class MyDatabaseDriver extends AbstractPdoDriver
{
    protected string $dsn_template = 'mydb:host=%s;dbname=%s';
    public function connect(): void { }
}
```

Register in configuration, instantiated via factory.

### 3. Custom Middleware

Extend `Core\Middleware`:

```php
class MyMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        // One-time setup
        return true;
    }

    public function handle(array $args): bool
    {
        // Per-request logic
        return true; // continue pipeline, false = short-circuit
    }
}
```

Add to routes or global middleware configuration.

### 4. Custom Helpers

Global helper functions added via `config('app.helpers')` or direct inclusion.

## File Organization

```
src/
├── Application.php          # Main orchestrator
├── Core/                    # Core interfaces and classes
│   ├── Controller.php
│   ├── Middleware.php
│   ├── Route.php
│   ├── RouteGroup.php
│   ├── Config.php
│   ├── Logger.php
│   ├── CacheManager.php
│   ├── ConnectionManager.php
│   └── UploadedFile(s).php
├── Cache/Adapters/         # Cache implementations
├── Database/Drivers/Pdo/   # Database driver implementations
├── Factories/Http/         # PSR-17 HTTP factories
├── Exceptions/             # Framework exceptions
├── Classes/                # Utility classes
└── Helpers/                # JWT, UUID, Cipher, etc.

tests/
├── CoreTest.php            # Application tests
├── Core/                   # Component tests
├── Cache/                  # Adapter tests
└── Database/               # Driver tests
```

## PSR Compliance

| PSR | Component | Details |
|-----|-----------|---------|
| PSR-3 | Logger | Monolog-based logging interface |
| PSR-7 | HTTP Messages | Guzzle PSR-7 implementation |
| PSR-11 | Container | league/container for DI |
| PSR-16 | Cache | CacheManager implements interface |
| PSR-17 | HTTP Factories | Guzzle factories in `Factories/Http/` |

## Design Patterns Used

- **Middleware Pattern** — Pipeline of interceptors
- **Strategy Pattern** — Pluggable cache/database implementations
- **Factory Pattern** — HTTP message creation
- **Singleton Pattern** — Shared container services
- **Template Method** — Controller and Middleware base classes

## Performance Considerations

- Configuration caching via APCu when available
- Lazy-loading of services via container
- Early middleware short-circuiting to avoid controller dispatch
- Connection pooling for database
- Cache-friendly middleware ordering (cache lookups before heavy operations)

---

**Last Updated:** 2026-03-15
**Framework Version:** 0.0.36+
