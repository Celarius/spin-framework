# Generic LLM Instructions — SPIN Framework

Guidance for ChatGPT, Gemini, Claude, and other LLMs when assisting with the SPIN Framework.

## What is SPIN?

**SPIN Framework** is a lightweight, modern PHP 8+ web framework designed for rapid development of web applications and REST APIs. It emphasizes convention over configuration, clean separation of concerns, and strict adherence to PSR (PHP Standard Recommendations).

- **Repository:** `celarius/spin-framework`
- **Namespace:** `Spin\` (PSR-4 mapped to `src/`)
- **Current version:** See `VERSION` file (pre-1.0, actively developed)
- **License:** MIT
- **Author:** Kim Sandell

## Core Philosophy

SPIN prioritizes:
1. **JSON-based routing** — no PHP route definitions
2. **Configuration over code** — JSON config with environment variable expansion
3. **Middleware pipeline** — structured request/response handling with clear execution order
4. **PSR compliance** — standard interfaces for logging, HTTP messages, caching, dependency injection
5. **Developer productivity** — global helper functions reduce boilerplate
6. **Framework stability** — conservative API changes; breaking changes are documented

## Architecture Overview

### Entry Point: Application Class

The `Spin\Application` class orchestrates the entire request/response lifecycle:

```
1. Bootstrap application and register services
2. Load configuration (JSON files with macro expansion)
3. Parse JSON routes
4. Execute middleware pipeline:
   - Global "before" middleware
   - Route group "before" middleware
   - Route controller handler (HTTP method)
   - Route group "after" middleware
   - Global "after" middleware
5. Return response to client
```

### Key Services

| Service | Responsibility | Access |
|---------|-----------------|--------|
| `Config` | Loads/retrieves configuration from JSON | `config()` helper |
| `Controller` | Base class for HTTP handlers | Extend for handlers |
| `Middleware` | Base class for pipeline handlers | Extend for middleware |
| `RouteGroup` | Organizes routes with shared prefix/middleware | JSON routes |
| `Route` | Maps HTTP method + path to controller | JSON routes |
| `CacheManager` | Manages cache adapters (APCu, Redis, File) | `cache()` helper |
| `ConnectionManager` | Pools database connections | `app()->get('connections')` |
| `Logger` | Wraps Monolog with PSR-3 interface | `logger()` helper |

### JSON-Based Routing

Routes are **never** defined in PHP. Instead, use JSON files:

```json
{
  "common": {
    "prefix": "/api/v1",
    "middleware": ["cors", "auth"]
  },
  "groups": [
    {
      "prefix": "/users",
      "middleware": ["rate-limit"],
      "routes": [
        {
          "path": "/:id",
          "controller": "UserController",
          "methods": ["GET", "PUT", "DELETE"]
        },
        {
          "path": "/",
          "controller": "UserController",
          "methods": ["GET", "POST"]
        }
      ]
    }
  ],
  "routes": [
    {
      "path": "/health",
      "controller": "HealthController",
      "methods": ["GET"]
    }
  ]
}
```

**Key points:**
- `common`: global prefix and middleware for all routes
- `groups`: organize routes by prefix with optional middleware
- `routes`: root-level routes
- URL parameters use `:paramName` syntax (e.g., `/:id`, `/:slug`)
- `methods` array specifies HTTP verbs (GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS)

See `doc/Routing.md` for full specification.

### JSON-Based Configuration

Configuration files follow `config-{environment}.json` pattern with `${env:VARIABLE}` macro support:

```json
{
  "app": {
    "name": "My API",
    "debug": "${env:DEBUG:false}",
    "timezone": "UTC"
  },
  "database": {
    "default": "mysql",
    "mysql": {
      "host": "${env:DB_HOST}",
      "port": 3306,
      "database": "${env:DB_NAME}",
      "username": "${env:DB_USER}",
      "password": "${env:DB_PASS}"
    }
  },
  "cache": {
    "default": "redis",
    "adapters": {
      "redis": {
        "host": "localhost",
        "port": 6379,
        "ttl": 3600
      }
    }
  }
}
```

**Macro syntax:** `${env:VAR_NAME:default_value}`

See `doc/Configuration.md` for full specification.

## Essential Code Patterns

### Controllers

Controllers extend `Spin\Core\Controller` and implement HTTP method handlers:

```php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $userId = $args['id'] ?? null;

        if (!$userId) {
            return responseJson(['error' => 'Missing ID'], 400);
        }

        $user = app()->get('models.user')->findById($userId);

        if (!$user) {
            return responseJson(['error' => 'User not found'], 404);
        }

        return responseJson($user);
    }

    public function handlePOST(array $args): ResponseInterface
    {
        $body = getRequest()->getParsedBody();

        try {
            $user = app()->get('models.user')->create($body);
            return responseJson($user, 201);
        } catch (\InvalidArgumentException $e) {
            return responseJson(['error' => $e->getMessage()], 400);
        }
    }

    public function handleDELETE(array $args): ResponseInterface
    {
        $userId = $args['id'] ?? null;

        if (!$userId) {
            return responseJson(['error' => 'Missing ID'], 400);
        }

        $deleted = app()->get('models.user')->deleteById($userId);

        return responseJson(
            ['deleted' => $deleted],
            $deleted ? 204 : 404
        );
    }
}
```

**Handler methods:** `handleGET()`, `handlePOST()`, `handlePUT()`, `handleDELETE()`, `handlePATCH()`, `handleHEAD()`, `handleOPTIONS()`

**Conventions:**
- `$args` contains URL parameters as array keys (e.g., `['id' => '123']`)
- Always return `ResponseInterface`
- Use `responseJson()` for JSON responses
- Use `response()` for plain text responses
- Throw exceptions for errors; framework catches and converts to responses

### Middleware

Middleware extends `Spin\Core\Middleware` and implements two lifecycle methods:

```php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class AuthMiddleware extends Middleware
{
    /**
     * Initialize phase: called once when middleware is instantiated
     * Use this to read configuration and prepare state
     */
    public function initialize(array $args): bool
    {
        $secret = config('auth.secret');

        if (!$secret) {
            logger()->error('Auth middleware: secret not configured');
            return false; // Initialization failed
        }

        return true; // Ready to process requests
    }

    /**
     * Handle phase: called for every request
     * Return false to short-circuit pipeline, true to continue
     */
    public function handle(array $args): bool
    {
        $request = getRequest();
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {
            logger()->warning('Missing Authorization header');
            return false; // Stop pipeline; unauthenticated
        }

        try {
            $token = str_replace('Bearer ', '', $authHeader);
            $decoded = app()->get('helpers.jwt')->validate($token);

            // Store user data in request for controller access
            $request->withAttribute('user', $decoded);

            return true; // Continue to next middleware/controller
        } catch (\Exception $e) {
            logger()->warning('Invalid token: ' . $e->getMessage());
            return false; // Stop pipeline; authentication failed
        }
    }
}
```

**Pipeline execution order:**
1. Global "before" middleware (auth, CORS)
2. Route group "before" middleware
3. Controller handler
4. Route group "after" middleware
5. Global "after" middleware

Returning `false` from `handle()` stops the pipeline immediately.

### Dependency Injection

Access the DI container via the `app()` global helper:

```php
// Register a service
app()->addDefinition('service.name', function ($container) {
    return new MyService(
        $container->get('database'),
        $container->get('cache')
    );
});

// Retrieve a service
$service = app()->get('service.name');

// Access common built-in services
$config = config('database.default');    // or config() helper
$db = app()->get('database');
$cache = cache();                        // or cache() helper
$logger = logger();                      // or logger() helper
$request = getRequest();
$response = getResponse();
```

### Testing with PHPUnit

Test files mirror `src/` structure under `tests/`:

```php
declare(strict_types=1);
namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;

class UserControllerTest extends TestCase
{
    private UserController $controller;

    protected function setUp(): void
    {
        $this->controller = new UserController();
    }

    public function testHandleGETReturnsUserForValidId(): void
    {
        // Arrange
        $args = ['id' => '123'];

        // Act
        $response = $this->controller->handleGET($args);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testHandleGETReturns404ForInvalidId(): void
    {
        // Arrange
        $args = ['id' => 'invalid'];

        // Act
        $response = $this->controller->handleGET($args);

        // Assert
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHandleGETReturns400WithoutId(): void
    {
        // Arrange
        $args = [];

        // Act
        $response = $this->controller->handleGET($args);

        // Assert
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @dataProvider provideInvalidIds
     */
    public function testHandleGETRejectsInvalidIds($id): void
    {
        $response = $this->controller->handleGET(['id' => $id]);
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    public static function provideInvalidIds(): array
    {
        return [
            'empty' => [''],
            'null' => [null],
            'negative' => [-1],
        ];
    }
}
```

## Global Helper Functions

These are always available without imports:

| Function | Purpose | Example |
|----------|---------|---------|
| `app()` | Get DI container | `app()->get('service')` |
| `config(string $key, $default = null)` | Get configuration value | `config('database.default')` |
| `env(string $name, $default = null)` | Get environment variable | `env('DB_HOST', 'localhost')` |
| `getRequest()` | Get current HTTP request | `getRequest()->getMethod()` |
| `getResponse()` | Get response being built | `getResponse()->getStatusCode()` |
| `response(string $content, int $status = 200)` | Create plain text response | `response('OK', 200)` |
| `responseJson($data, int $status = 200)` | Create JSON response | `responseJson(['id' => 1], 201)` |
| `logger()` | Get logger instance | `logger()->info('Event')` |
| `cache()` | Get cache instance | `cache()->get('key')` |

## Coding Standards

Follow these strictly:

1. **Strict types declaration** (top of every file):
   ```php
   declare(strict_types=1);
   ```

2. **Explicit type hints** (no `mixed` unless unavoidable):
   ```php
   public function process(int $id, array $data): string { }
   ```

3. **PSR-4 namespace-to-path mapping:**
   ```
   Class: Spin\Cache\Adapters\RedisCacheAdapter
   File:  src/Cache/Adapters/RedisCacheAdapter.php
   ```

4. **Docblocks on public methods:**
   ```php
   /**
    * Fetch user by ID.
    *
    * @param int $id
    * @return array|null
    * @throws UserNotFoundException
    */
   public function findById(int $id): ?array { }
   ```

5. **Constants in UPPER_SNAKE_CASE:**
   ```php
   public const MAX_RETRIES = 3;
   private const TIMEOUT_SECONDS = 30;
   ```

6. **Properties with visibility and type:**
   ```php
   private string $apiKey;
   protected int $maxConnections;
   public float $timeout = 5.0;
   ```

## Common Extension Points

### Adding a Cache Adapter

Extend `Spin\Cache\Adapters\AbstractCacheAdapter`:

```php
declare(strict_types=1);
namespace Spin\Cache\Adapters;

class CustomCacheAdapter extends AbstractCacheAdapter
{
    private $backend;

    public function initialize(array $config): bool
    {
        $this->backend = new Backend($config);
        return true;
    }

    public function get(string $key, $default = null)
    {
        $value = $this->backend->retrieve($key);
        return $value !== null ? $value : $default;
    }

    public function set(string $key, $value, $ttl = null): bool
    {
        return $this->backend->store($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->backend->remove($key);
    }

    public function clear(): bool
    {
        return $this->backend->flush();
    }
}
```

### Adding a Database Driver

Extend `Spin\Database\Drivers\Pdo\AbstractPdoDriver`:

```php
declare(strict_types=1);
namespace Spin\Database\Drivers\Pdo;

class CustomDriver extends AbstractPdoDriver
{
    protected string $driverName = 'custom';

    protected function buildDsn(array $config): string
    {
        return sprintf(
            'custom:host=%s;port=%d;dbname=%s',
            $config['host'] ?? 'localhost',
            $config['port'] ?? 5432,
            $config['database'] ?? ''
        );
    }

    protected function getDefaultOptions(): array
    {
        return [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
    }
}
```

## Pitfalls to Avoid

1. **Never define routes in PHP code**
   - ❌ Bad: `$app->get('/users', UserController::class);`
   - ✅ Good: Define in `routes.json`

2. **Never hardcode configuration**
   - ❌ Bad: `$apiKey = 'sk-12345';`
   - ✅ Good: `$apiKey = env('API_KEY');`

3. **Never use global variables**
   - ❌ Bad: `global $database;`
   - ✅ Good: `$db = app()->get('database');`

4. **Never put business logic in controllers**
   - ❌ Bad: Complex query logic in `handleGET()`
   - ✅ Good: Extract to service class

5. **Never modify response in middleware**
   - ❌ Bad: `getResponse()->withBody($newBody);`
   - ✅ Good: `getRequest()->withAttribute('user', $data);`

6. **Never short-circuit middleware inappropriately**
   - ❌ Bad: Always return `false` regardless of validation
   - ✅ Good: Return `false` only on actual failure

7. **Never instantiate cache/db directly**
   - ❌ Bad: `new RedisCacheAdapter($config);`
   - ✅ Good: `cache();` (pre-configured)

## File Locations

| Path | Purpose |
|------|---------|
| `src/` | Framework source code (PSR-4 `Spin\`) |
| `src/Core/` | Core classes (Controller, Middleware, Config, etc.) |
| `src/Cache/Adapters/` | Cache implementations |
| `src/Database/Drivers/Pdo/` | Database drivers |
| `src/Helpers/` | Utility helpers (JWT, UUID, Hash, Cipher) |
| `src/Factories/Http/` | PSR-17 HTTP message factories |
| `src/Exceptions/` | Custom exception classes |
| `tests/` | PHPUnit test suite (mirrors `src/`) |
| `doc/` | Feature documentation |
| `.claude/` | Claude Code configuration |
| `.github/` | GitHub-specific files (workflows, Copilot instructions) |

## Dependencies

Core dependencies:

| Package | Purpose | When Used |
|---------|---------|-----------|
| `nikic/fast-route` | Route matching | Application bootstrapping |
| `guzzlehttp/guzzle` | PSR-7 HTTP messages | All request/response handling |
| `monolog/monolog` | Logging | `logger()` helper |
| `firebase/php-jwt` | JWT tokens | Authentication |
| `league/container` | DI container | `app()` helper, service resolution |
| `ramsey/uuid` | UUID generation | `helpers.uuid` service |
| `predis/predis` | Redis client | `RedisCacheAdapter` |
| `ext-apcu` *(optional)* | Fast in-memory cache | `ApcuCacheAdapter` |

## When to Ask for Clarification

Stop and ask before proceeding in these scenarios:

1. **Unclear requirements** — "Should this endpoint return 200 or 204 on delete?"
2. **API design decisions** — "Should this be nested or flat routing?"
3. **Performance implications** — "Will caching this affect consistency?"
4. **Breaking changes** — "Can we change the global helper signature?"
5. **Cross-cutting concerns** — "Where should logging be added?"
6. **Test coverage** — "What behavior is the edge case testing?"
7. **Configuration defaults** — "What should the default cache adapter be?"

## Key Files to Reference

- `src/Application.php` — Request/response orchestrator
- `src/Core/Controller.php` — Base controller class
- `src/Core/Middleware.php` — Base middleware class
- `doc/Routing.md` — Complete routing specification
- `doc/Configuration.md` — Configuration system details
- `CHANGELOG.md` — Version history and breaking changes
- `composer.json` — Dependencies and PSR-4 mapping

## Summary

SPIN Framework excels in:
- **Simplicity** — clear, predictable patterns
- **JSON configuration** — environment-specific, macro-expandable
- **Middleware** — well-defined pipeline order
- **PSR compliance** — interoperable with other PHP libraries
- **Global helpers** — reduced boilerplate code

When assisting with SPIN code:
- Prefer JSON route/config files over PHP definitions
- Use global helpers instead of container access
- Follow PSR-4 naming and strict typing
- Test both success and failure paths
- Document breaking changes explicitly

## Versioning and Changelog

After completing any feature or fix, always:

1. **Add a `CHANGELOG.md` entry** under the current version heading using bold category prefixes:
   - `**Feature:**` — new capabilities
   - `**Fix:**` — bug corrections
   - `**Breaking:**` — backwards-incompatible changes (include migration guidance)
   - `**Documentation:**` — doc-only changes

2. **Bump the version** in all three files consistently — they must always match:
   - `VERSION` — plain semver string (e.g. `0.1.0`)
   - `composer.json` — `"version"` field
   - `package.json` — `"version"` field

Never hardcode a specific version number in documentation or instruction files; always read the current version from the `VERSION` file.
