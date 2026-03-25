# Claude AI Instructions — SPIN Framework

Comprehensive guidance for Claude Code when working in the SPIN Framework repository.

## Framework Essentials

**SPIN Framework** is a lightweight PHP 8+ web framework for building web apps and REST APIs.

- **Package name:** `celarius/spin-framework`
- **Root namespace:** `Spin\` (maps to `src/` via PSR-4)
- **Author:** Kim Sandell
- **License:** MIT
- **Current version:** See `VERSION` file (pre-1.0, actively developed)
- **Repository:** GitHub at `celarius/spin-framework`
- **Companion skeleton project:** `celarius/spin-skeleton` (demonstrates framework usage)

## Project Structure

```
.claude/
  settings.json                    # Claude Code configuration
  claude-instructions.md           # This file
  llm-instructions.md              # Generic LLM guidance
.github/
  copilot-instructions.md          # GitHub Copilot guidance
src/
  Application.php                  # Main orchestrator for request/response lifecycle
  Core/
    Controller.php                 # Base controller class
    Middleware.php                 # Base middleware class
    Config.php                     # Configuration loader
    RouteGroup.php                 # Route grouping
    Route.php                      # Route definition
    CacheManager.php               # Cache orchestration
    ConnectionManager.php          # Database connection pooling
    Logger.php                     # Logging abstraction
    UploadedFile.php               # File upload handling
  Cache/
    Adapters/
      AbstractCacheAdapter.php      # Base for cache adapters
      ApcuCacheAdapter.php          # APCu in-memory cache
      RedisCacheAdapter.php         # Redis distributed cache
      FileCacheAdapter.php          # File-based cache
  Database/
    Drivers/
      Pdo/                          # PDO drivers for MySQL, PostgreSQL, SQLite, etc.
  Helpers/
    Cipher.php                     # Encryption/decryption utilities
    JWT.php                        # JWT token generation/validation
    UUID.php                       # UUID generation
    Hash.php                       # Password hashing
  Factories/
    Http/                          # PSR-17 HTTP message factories
  Classes/
    RequestIdClass.php             # Request ID tracking
  Exceptions/                      # Framework exception classes
tests/                             # PHPUnit test suite (mirrors src/)
doc/                               # Feature documentation
  Routing.md
  Configuration.md
  Caching.md
  Database.md
```

## Architectural Decisions

### JSON-Based Routing

Routes are **never** defined in PHP code. Instead, use JSON route files with three top-level keys:

```json
{
  "common": {
    "prefix": "/api/v1",
    "middleware": ["auth", "validate"]
  },
  "groups": [
    {
      "prefix": "/users",
      "middleware": ["rate-limit"],
      "routes": [
        {
          "path": "/:id",
          "controller": "UserController",
          "methods": ["GET", "PUT"]
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

See `doc/Routing.md` for complete specification.

### JSON-Based Configuration

Configuration files follow the pattern `config-{environment}.json` and support `${env:VARIABLE_NAME}` macro expansion:

```json
{
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
        "port": 6379
      }
    }
  }
}
```

See `doc/Configuration.md` for complete specification.

### Middleware Pipeline

Middleware executes in a strict order:

1. **Global "before" middleware** — early interception (authentication, CORS)
2. **RouteGroup "before" middleware** — group-level setup
3. **Controller handler** — business logic (`handleGET`, `handlePOST`, etc.)
4. **RouteGroup "after" middleware** — group-level cleanup
5. **Global "after" middleware** — final response transformation

Middleware classes implement:

```php
public function initialize(array $args): bool  // Setup phase; read config, prepare state
public function handle(array $args): bool       // Per-request; return false to short-circuit
```

Returning `false` from `handle()` stops the pipeline immediately.

### PSR Compliance

The framework strictly implements:

- **PSR-3 (Logging)** — `Psr\Log\LoggerInterface`
- **PSR-7 (HTTP Messages)** — `Psr\Http\Message\RequestInterface` and `ResponseInterface`
- **PSR-11 (Container)** — `Psr\Container\ContainerInterface` for dependency injection
- **PSR-16 (Simple Cache)** — `Psr\SimpleCache\CacheInterface`
- **PSR-17 (HTTP Factories)** — `Psr\Http\Message\RequestFactoryInterface`, etc.

### Global Helper Functions

These are **always available** without importing. Use them instead of accessing the container directly:

| Helper | Purpose | Example |
|--------|---------|---------|
| `app()` | Get DI container | `app()->get('service.name')` |
| `config(string $key, $default = null)` | Read configuration | `config('database.default')` |
| `getRequest()` | Get current HTTP request | `$uri = getRequest()->getUri();` |
| `getResponse()` | Get response object being built | `getResponse()->getStatusCode()` |
| `response($content, int $status = 200)` | Create text response | `response('OK', 200)` |
| `responseJson($data, int $status = 200)` | Create JSON response | `responseJson(['id' => 1])` |
| `logger()` | Get logger instance | `logger()->info('Event');` |
| `cache()` | Get cache instance | `cache()->get('key');` |
| `env(string $name, $default = null)` | Read environment variable | `env('APP_ENV', 'dev')` |

## Extending the Framework

### Adding a Controller

Controllers extend `Spin\Core\Controller` and override HTTP method handlers:

```php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    /**
     * Handle GET /users/:id
     *
     * @param array $args URL parameters: ['id' => '123']
     */
    public function handleGET(array $args): ResponseInterface
    {
        $userId = $args['id'] ?? null;
        if (!$userId) {
            return responseJson(['error' => 'ID required'], 400);
        }

        $user = app()->get('models.user')->findById($userId);
        if (!$user) {
            return responseJson(['error' => 'Not found'], 404);
        }

        return responseJson($user);
    }

    /**
     * Handle POST /users
     */
    public function handlePOST(array $args): ResponseInterface
    {
        $body = getRequest()->getParsedBody();
        $user = app()->get('models.user')->create($body);

        return responseJson($user, 201);
    }
}
```

**Key points:**
- Each HTTP method gets a handler: `handleGET()`, `handlePOST()`, `handlePUT()`, `handleDELETE()`, `handlePATCH()`, `handleHEAD()`, `handleOPTIONS()`
- URL parameters are passed as `$args` array
- Always return `ResponseInterface`
- Use global helpers (`response()`, `responseJson()`) for response construction
- Throw exceptions for errors; the framework catches and handles them

### Adding Middleware

Middleware extends `Spin\Core\Middleware` and implements initialization and request handling:

```php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class AuthMiddleware extends Middleware
{
    /**
     * Setup phase: called once per middleware instance
     * Read configuration, establish connections, etc.
     */
    public function initialize(array $args): bool
    {
        // Check that required config is present
        if (!config('auth.secret')) {
            logger()->error('Auth middleware: secret not configured');
            return false;
        }
        return true;
    }

    /**
     * Per-request phase: called for every request
     * Return false to stop pipeline, true to continue
     */
    public function handle(array $args): bool
    {
        $request = getRequest();
        $token = $request->getHeaderLine('Authorization') ?: '';

        if (empty($token)) {
            logger()->warning('Missing Authorization header');
            return false; // Stop pipeline; unauthenticated request
        }

        // Validate token
        try {
            $decoded = app()->get('helpers.jwt')->validate($token);
            // Store in request attributes for controller access
            getRequest()->withAttribute('user', $decoded);
            return true; // Continue to next middleware/controller
        } catch (\Exception $e) {
            logger()->warning('Invalid token: ' . $e->getMessage());
            return false; // Stop pipeline; bad token
        }
    }
}
```

Register in `config-{env}.json`:

```json
{
  "middleware": {
    "global": {
      "before": ["cors", "auth"],
      "after": ["response-time"]
    }
  }
}
```

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
        // Initialize your backend
        $this->backend = new YourCacheBackend($config);
        return true;
    }

    public function get(string $key, $default = null)
    {
        $value = $this->backend->retrieve($key);
        return $value !== null ? $value : $default;
    }

    public function set(string $key, $value, $ttl = null): bool
    {
        $expiresAt = $ttl ? time() + $ttl : null;
        return $this->backend->store($key, $value, $expiresAt);
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

Register in config:

```json
{
  "cache": {
    "default": "custom",
    "adapters": {
      "custom": {
        "class": "Spin\\Cache\\Adapters\\CustomCacheAdapter"
      }
    }
  }
}
```

### Adding a Database Driver

Create a new driver extending the PDO base class:

```php
declare(strict_types=1);
namespace Spin\Database\Drivers\Pdo;

class CustomDatabaseDriver extends \Spin\Database\Drivers\Pdo\AbstractPdoDriver
{
    protected string $driverName = 'customdb';

    protected function buildDsn(array $config): string
    {
        return sprintf(
            'customdb:host=%s;port=%d;dbname=%s',
            $config['host'] ?? 'localhost',
            $config['port'] ?? 5432,
            $config['database'] ?? ''
        );
    }

    protected function getDefaultOptions(): array
    {
        return [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            // Driver-specific options
        ];
    }
}
```

Register in config:

```json
{
  "database": {
    "drivers": {
      "customdb": "Spin\\Database\\Drivers\\Pdo\\CustomDatabaseDriver"
    }
  }
}
```

## Testing Patterns

All tests live in `tests/` mirroring the `src/` structure. Use PHPUnit 10+.

### Testing Controllers

```php
declare(strict_types=1);
namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use Spin\Core\Application;
use Psr\Http\Message\ServerRequestInterface;

class UserControllerTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application('test');
    }

    public function testGetUserReturnsJsonResponse(): void
    {
        $response = $this->app->dispatch(
            $this->createRequest('GET', '/users/123')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    private function createRequest(
        string $method,
        string $path,
        $body = null
    ): ServerRequestInterface {
        // Build PSR-7 ServerRequest
    }
}
```

### Testing Middleware

```php
public function testAuthMiddlewareRejectsRequestWithoutToken(): void
{
    $middleware = new AuthMiddleware();
    $middleware->initialize(config('middleware.auth') ?? []);

    $request = $this->createRequest('GET', '/api/users')
        ->withoutHeader('Authorization');

    $result = $middleware->handle(['request' => $request]);
    $this->assertFalse($result);
}

public function testAuthMiddlewareAcceptsValidToken(): void
{
    $middleware = new AuthMiddleware();
    $middleware->initialize(config('middleware.auth') ?? []);

    $token = 'valid.jwt.token';
    $request = $this->createRequest('GET', '/api/users')
        ->withAddedHeader('Authorization', "Bearer {$token}");

    $result = $middleware->handle(['request' => $request]);
    $this->assertTrue($result);
}
```

### Running Tests

```bash
# Windows
.\phpunit.cmd

# Linux/macOS
./vendor/bin/phpunit

# Specific test file
./vendor/bin/phpunit tests/Controllers/UserControllerTest.php

# With coverage (requires Xdebug or PCOV)
./vendor/bin/phpunit --coverage-html coverage/
```

## Coding Conventions

Follow these strictly when adding or modifying code:

1. **Strict types declaration** — Always include at the top of every PHP file:
   ```php
   declare(strict_types=1);
   ```

2. **Explicit type hints** — No `mixed` types unless absolutely unavoidable:
   ```php
   // Good
   public function process(int $id, array $data): void { }

   // Bad
   public function process($id, $data) { }
   ```

3. **PSR-4 namespace mapping** — Class locations must match namespaces:
   ```
   Spin\Core\Controller → src/Core/Controller.php
   Spin\Cache\Adapters\RedisCacheAdapter → src/Cache/Adapters/RedisCacheAdapter.php
   ```

4. **Docblocks** — Add docblocks to all public methods and class-level property annotations:
   ```php
   class UserController extends Controller
   {
       /** @var UserRepository */
       private UserRepository $userRepository;

       /**
        * Retrieve a user by ID.
        *
        * @param array $args URL parameters with 'id' key
        * @return ResponseInterface JSON response with user data or error
        * @throws UserNotFoundException if user does not exist
        */
       public function handleGET(array $args): ResponseInterface
       {
           // ...
       }
   }
   ```

5. **Constants** — Use UPPER_SNAKE_CASE:
   ```php
   public const DEFAULT_TIMEOUT = 30;
   private const MAX_RETRIES = 3;
   ```

6. **Properties** — Use camelCase; always declare visibility and type:
   ```php
   private string $apiKey;
   protected int $maxConnections;
   public float $timeout;
   ```

7. **Methods** — Use camelCase with HTTP method prefix for handlers:
   ```php
   public function handleGET(array $args): ResponseInterface { }
   public function handlePOST(array $args): ResponseInterface { }
   private function validateInput(array $data): bool { }
   ```

8. **Comments** — Keep comments brief and purpose-focused:
   ```php
   // Good
   // Retry failed requests with exponential backoff

   // Bad
   // loop through items
   ```

## Common Tasks

### Logging

```php
logger()->debug('User login attempt', ['user_id' => $userId]);
logger()->info('Cache hit for key: ' . $key);
logger()->warning('Rate limit approaching');
logger()->error('Database connection failed', ['exception' => $e]);
logger()->critical('Out of memory');
```

### Caching

```php
$value = cache()->get('user:123');
if (!$value) {
    $value = fetchExpensiveData();
    cache()->set('user:123', $value, 3600); // TTL in seconds
}

cache()->delete('user:123');
cache()->clear(); // Flush entire cache
```

### Configuration

```php
$dbHost = config('database.mysql.host');
$timeout = config('app.timeout', 30); // With default
$allDbConfig = config('database');
```

### Environment Variables

```php
$appEnv = env('APP_ENV', 'dev');
$debug = env('DEBUG') === 'true';
$apiKey = env('API_KEY'); // Will be null if not set
```

### Database Operations

```php
$connection = app()->get('database')->getConnection('mysql');
$result = $connection->query('SELECT * FROM users WHERE id = ?', [123]);
$users = $connection->fetchAll('SELECT * FROM users');
$lastId = $connection->lastInsertId();
```

### Dependency Injection

```php
// Register a service
app()->addDefinition('service.name', function ($container) {
    return new MyService($container->get('other.service'));
});

// Retrieve a service
$service = app()->get('service.name');
```

## Anti-Patterns to Avoid

1. **Don't hardcode routes in PHP** — Use JSON route files exclusively
   ```php
   // Bad
   $app->get('/users', UserController::class);

   // Good: Define routes in routes.json
   ```

2. **Don't use global variables** — Use the DI container instead
   ```php
   // Bad
   global $database;

   // Good
   $db = app()->get('database');
   ```

3. **Don't mix business logic in controllers** — Extract to services
   ```php
   // Bad
   public function handleGET(array $args): ResponseInterface {
       $sql = "SELECT * FROM users WHERE id = " . $args['id'];
       // ... complex query logic
   }

   // Good
   public function handleGET(array $args): ResponseInterface {
       $user = app()->get('services.user')->getById($args['id']);
       return responseJson($user);
   }
   ```

4. **Don't short-circuit middleware pipeline** — Return `false` only when appropriate
   ```php
   // Bad: Always returns false
   public function handle(array $args): bool { return false; }

   // Good: Only returns false when condition fails
   public function handle(array $args): bool {
       return isValid($args) ? true : false;
   }
   ```

5. **Don't modify response directly in middleware** — Set attributes instead
   ```php
   // Bad
   getResponse()->withBody($newBody);

   // Good
   getRequest()->withAttribute('user', $userData);
   ```

6. **Don't create connection pools manually** — Use ConnectionManager
   ```php
   // Bad
   $pdo = new PDO($dsn, $user, $pass);

   // Good
   $conn = app()->get('connections')->getConnection('mysql');
   ```

7. **Don't instantiate cache adapters directly** — Use CacheManager
   ```php
   // Bad
   $cache = new RedisCacheAdapter($config);

   // Good
   $cache = cache(); // Already resolved
   ```

## What NOT to Change Without Discussion

These are critical public APIs consumed by downstream applications:

- **Global helper names/signatures** — `config()`, `response()`, `responseJson()`, `getRequest()`, `getResponse()`, `logger()`, `cache()`, `env()`, `app()`
- **PSR-4 namespace-to-path mapping** — declared in `composer.json`
- **JSON route file format** — schema changes break all consumer apps
- **Public class APIs in `src/Core/`** — downstream dependency detection is weak
- **Exception types and inheritance** — catch clauses depend on hierarchy

Always document breaking changes in [CHANGELOG.md](../CHANGELOG.md) with migration guidance.

## When to Ask for Clarification

Stop and ask before proceeding in these scenarios:

1. **Unclear API contract** — "Should this method throw or return null?"
2. **Performance implications** — "Will this change affect cache hit rates?"
3. **Breaking changes** — "Should we version this API or provide a migration path?"
4. **Cross-cutting concerns** — "Should logging be added to all cache operations?"
5. **Design conflicts** — "Does this violate the middleware pipeline design?"
6. **Test coverage uncertainty** — "What's the expected behavior in edge cases?"
7. **Configuration defaults** — "What should be the default cache adapter?"

## Dependencies

| Package | Purpose | Notes |
|---------|---------|-------|
| `nikic/fast-route` | URL routing | O(1) route matching; consulted in Application.php |
| `guzzlehttp/guzzle` | PSR-7 HTTP messages | Immutable; used throughout for requests/responses |
| `monolog/monolog` | PSR-3 logging | Singleton via Logger.php; use `logger()` helper |
| `firebase/php-jwt` | JWT token support | Used in Helpers/JWT.php; handles signing/validation |
| `league/container` | PSR-11 DI container | Core to dependency injection; use `app()` helper |
| `ramsey/uuid` | UUID generation | Used in Helpers/UUID.php for unique identifiers |
| `predis/predis` | Redis client | Optional; used by RedisCacheAdapter |
| `ext-apcu` *(optional)* | In-memory cache | Required for ApcuCacheAdapter; fast, process-local |

## File Organization

When adding new code:

1. **Follow PSR-4** — Namespace and directory must align
2. **Add tests** — Mirror the structure under `tests/`
3. **Document public APIs** — Docblocks on all public methods
4. **Update CHANGELOG.md** — Add a bullet under the current version heading using bold category prefixes: `**Feature:**`, `**Fix:**`, `**Breaking:**`, `**Documentation:**`
5. **Bump the version** — When completing a release, update these three files consistently: `VERSION` (plain text), `composer.json` (`"version"` field), `package.json` (`"version"` field). Never update only a subset.
6. **Reference doc files** — Link to `doc/` for feature details

## Development Workflow

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Write code and tests
3. Run full test suite: `./vendor/bin/phpunit`
4. Run with coverage if modifying core: `./vendor/bin/phpunit --coverage-html coverage/`
5. Add a `CHANGELOG.md` entry under the current version section
6. Bump `VERSION`, `composer.json`, and `package.json` to the new version (all three must stay in sync)
7. Commit with clear message: `git commit -m "feat: add new feature"`
8. Create PR to `develop` branch
9. Ensure tests pass and coverage is maintained

## References

- [doc/Routing.md](../doc/Routing.md) — Complete routing specification
- [doc/Configuration.md](../doc/Configuration.md) — Configuration system details
- [doc/Caching.md](../doc/Caching.md) — Cache adapters and usage
- [doc/Database.md](../doc/Database.md) — Database drivers and connections
- [CHANGELOG.md](../CHANGELOG.md) — Version history and breaking changes
