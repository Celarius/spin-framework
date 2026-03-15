# GitHub Copilot Instructions — SPIN Framework

Guidance for GitHub Copilot when generating code in the SPIN Framework repository.

## Project Overview

**SPIN Framework** (`celarius/spin-framework`) is a lightweight PHP 8+ web framework emphasizing:
- **JSON-based routing** — routes declared in JSON files, never in PHP
- **Configuration as JSON** — environment-specific config files with macro expansion
- **Middleware pipeline** — structured request/response handling
- **PSR compliance** — PSR-3, PSR-7, PSR-11, PSR-16, PSR-17 implementations
- **Dependency injection** — centralized service container
- **Global helpers** — convenient functions for common operations

## Repository Structure

```
src/
  Application.php                  # Request/response orchestrator
  Core/
    Controller.php                 # HTTP handler base class
    Middleware.php                 # Pipeline handler base class
    Config.php
    Route.php
    RouteGroup.php
    CacheManager.php
    ConnectionManager.php
    Logger.php
  Cache/Adapters/                  # Cache implementations (APCu, Redis, File)
  Database/Drivers/Pdo/            # Database drivers (MySQL, PostgreSQL, SQLite, etc.)
  Helpers/                         # Cipher, JWT, UUID, Hash, ArrayToXml
  Factories/Http/                  # PSR-17 HTTP factories
  Exceptions/                      # Framework exception classes
tests/                             # PHPUnit tests (mirrors src/)
doc/                               # Feature documentation
```

## Naming Conventions

### Files and Directories
- **PSR-4 compliance** — namespace must match directory path
  - `Spin\Core\Controller` → `src/Core/Controller.php`
  - `Spin\Cache\Adapters\RedisCacheAdapter` → `src/Cache/Adapters/RedisCacheAdapter.php`
- **Class names** — PascalCase matching file name exactly
- **Interfaces** — suffix with `Interface` (e.g., `CacheInterface`)
- **Abstract classes** — prefix with `Abstract` (e.g., `AbstractCacheAdapter`)
- **Test files** — mirror source structure, suffix with `Test` (e.g., `UserControllerTest.php`)

### Code Identifiers
- **Constants** — UPPER_SNAKE_CASE
  ```php
  public const DEFAULT_TIMEOUT = 30;
  private const MAX_CONNECTIONS = 10;
  ```
- **Class properties** — camelCase with visibility and type declaration
  ```php
  private string $apiKey;
  protected int $maxRetries;
  public float $timeout;
  ```
- **Methods** — camelCase; HTTP handlers use prefix pattern
  ```php
  public function handleGET(array $args): ResponseInterface { }
  public function handlePOST(array $args): ResponseInterface { }
  private function validateInput(array $data): bool { }
  ```
- **Variables** — camelCase
  ```php
  $userId = 123;
  $userData = ['name' => 'John'];
  ```

## Code Style (PSR-12 + Strict Types)

### File Header
Every PHP file must start with:
```php
declare(strict_types=1);
namespace Spin\Core;
```

### Type Declarations
Always use explicit types; avoid `mixed`:
```php
// Good
public function process(int $id, array $data): string { }

// Bad
public function process($id, $data) { }
```

### Class Structure
```php
declare(strict_types=1);
namespace Spin\Core;

use Psr\Http\Message\ResponseInterface;

/**
 * Brief description of class purpose.
 *
 * @author Your Name
 */
class MyClass
{
    /** @var string */
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Method description.
     *
     * @param int $id
     * @return string
     */
    public function getData(int $id): string
    {
        return 'data';
    }
}
```

### Method Implementation
```php
public function handleGET(array $args): ResponseInterface
{
    $id = $args['id'] ?? null;

    if (!$id) {
        return responseJson(['error' => 'ID required'], 400);
    }

    try {
        $data = app()->get('service')->fetch($id);
        return responseJson($data);
    } catch (\Exception $e) {
        logger()->error('Fetch failed: ' . $e->getMessage());
        return responseJson(['error' => 'Server error'], 500);
    }
}
```

## Common Patterns

### Controllers
Extend `Spin\Core\Controller` and implement HTTP method handlers:

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
        $user = app()->get('models.user')->find($userId);
        return responseJson($user ?? ['error' => 'Not found'], $user ? 200 : 404);
    }

    public function handlePOST(array $args): ResponseInterface
    {
        $data = getRequest()->getParsedBody();
        $user = app()->get('models.user')->create($data);
        return responseJson($user, 201);
    }
}
```

### Middleware
Extend `Spin\Core\Middleware` and implement both methods:

```php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class AuthMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        return true;
    }

    public function handle(array $args): bool
    {
        $token = getRequest()->getHeaderLine('Authorization');

        if (!$token) {
            return false;
        }

        try {
            $decoded = app()->get('helpers.jwt')->validate($token);
            getRequest()->withAttribute('user', $decoded);
            return true;
        } catch (\Exception $e) {
            logger()->warning('Invalid token');
            return false;
        }
    }
}
```

### Services
Extract business logic into service classes (not in src/, but demonstrate pattern):

```php
declare(strict_types=1);
namespace App\Services;

class UserService
{
    public function getById(int $id): ?array
    {
        $cache = cache();
        $cacheKey = "user:{$id}";

        $user = $cache->get($cacheKey);
        if ($user) {
            return $user;
        }

        $user = $this->fetchFromDatabase($id);
        if ($user) {
            cache()->set($cacheKey, $user, 3600);
        }

        return $user;
    }

    private function fetchFromDatabase(int $id): ?array
    {
        // Database query logic
        return null;
    }
}
```

### Unit Tests
Mirror `src/` structure under `tests/`:

```php
declare(strict_types=1);
namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    public function testHandleGETReturnsUserData(): void
    {
        $response = new UserController();
        // Test logic
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testHandleGETReturns404ForInvalidId(): void
    {
        // Test missing user
    }
}
```

## Using Global Helpers

Prefer global helpers over container access:

```php
// Good
$config = config('database.mysql');
$logger = logger();
$cache = cache();
$response = responseJson($data);

// Less preferred
$container = app();
$config = $container->get('config')->get('database.mysql');
```

Available helpers: `app()`, `config()`, `env()`, `getRequest()`, `getResponse()`, `response()`, `responseJson()`, `logger()`, `cache()`

## Framework Dependencies

When suggesting imports or usage, reference these packages:

| Package | Use for |
|---------|---------|
| `nikic/fast-route` | Route matching (internal; don't instantiate directly) |
| `guzzlehttp/guzzle` | PSR-7 HTTP messages, RequestInterface, ResponseInterface |
| `monolog/monolog` | Logging via LoggerInterface |
| `firebase/php-jwt` | JWT token signing/validation via Helpers/JWT |
| `league/container` | Dependency injection via app() helper |
| `ramsey/uuid` | UUID generation via Helpers/UUID |
| `predis/predis` | Redis operations (if RedisCacheAdapter is used) |
| `ext-apcu` | Fast in-memory cache (ApcuCacheAdapter) |

## Anti-Patterns to Avoid

1. **Don't suggest PHP-defined routes** — Always point to JSON files
   ```php
   // Bad
   $app->get('/users/:id', UserController::class);

   // Good: Use routes.json instead
   ```

2. **Don't hardcode configuration** — Use config files and env vars
   ```php
   // Bad
   $apiKey = '12345';

   // Good
   $apiKey = env('API_KEY');
   ```

3. **Don't use global variables** — Use DI container
   ```php
   // Bad
   global $database;

   // Good
   $db = app()->get('database');
   ```

4. **Don't mix business logic in controllers** — Extract to services
   ```php
   // Bad: Business logic in handleGET
   public function handleGET(array $args): ResponseInterface {
       $sql = "SELECT * FROM users WHERE id = " . $args['id'];
   }

   // Good: Delegate to service
   public function handleGET(array $args): ResponseInterface {
       $user = app()->get('services.user')->getById($args['id']);
   }
   ```

5. **Don't bypass middleware** — Respect the pipeline order
   ```php
   // Bad: Middleware that always continues without checking
   public function handle(array $args): bool { return true; }

   // Good: Validate before continuing
   public function handle(array $args): bool {
       return $this->isValid() ? true : false;
   }
   ```

6. **Don't directly instantiate cache/database** — Use managers
   ```php
   // Bad
   $cache = new RedisCacheAdapter($config);

   // Good
   $cache = cache();
   ```

7. **Don't modify response inside middleware** — Use request attributes
   ```php
   // Bad: Can't modify response safely in pipeline
   getResponse()->withBody($newBody);

   // Good: Set data for controller to use
   getRequest()->withAttribute('userData', $data);
   ```

## PSR Standards Compliance

Suggest code that follows these standards:

- **PSR-3** — Use `Psr\Log\LoggerInterface` methods: `debug()`, `info()`, `warning()`, `error()`, `critical()`
- **PSR-7** — Use immutable message objects (`RequestInterface`, `ResponseInterface`)
- **PSR-11** — Use `Psr\Container\ContainerInterface` for DI (via `app()`)
- **PSR-16** — Use `Psr\SimpleCache\CacheInterface` methods: `get()`, `set()`, `delete()`, `clear()`
- **PSR-17** — Use message factories from `Psr\Http\Message\*FactoryInterface`

## Configuration Patterns

Config files use JSON with macro expansion:

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
  }
}
```

When suggesting configuration usage:
```php
$host = config('database.mysql.host');
$dbName = config('database.mysql.database');
$default = config('some.key', 'default-value'); // With fallback
```

## Routing Patterns

Routes are JSON-based; suggest checking `routes.json`:

```json
{
  "common": {
    "prefix": "/api/v1",
    "middleware": ["auth", "rate-limit"]
  },
  "groups": [
    {
      "prefix": "/users",
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
  ]
}
```

## Testing Guidance

When suggesting tests:

1. **Use PHPUnit** framework (v10+)
2. **Follow AAA pattern** — Arrange, Act, Assert
3. **Name tests descriptively** — `testHandleGETReturnsJsonResponse()`
4. **Test both success and failure** paths
5. **Use data providers** for multiple scenarios

```php
/**
 * @dataProvider provideInvalidUserIds
 */
public function testHandleGETRejectsInvalidId(int $id): void
{
    $response = $this->controller->handleGET(['id' => $id]);
    $this->assertEquals(400, $response->getStatusCode());
}

public static function provideInvalidUserIds(): array
{
    return [
        'zero' => [0],
        'negative' => [-1],
    ];
}
```

## Error Handling

Suggest try-catch patterns that log appropriately:

```php
try {
    $data = app()->get('service')->fetch($id);
    return responseJson($data);
} catch (NotFoundException $e) {
    logger()->info('Resource not found: ' . $e->getMessage());
    return responseJson(['error' => 'Not found'], 404);
} catch (\Exception $e) {
    logger()->error('Unexpected error: ' . $e->getMessage());
    return responseJson(['error' => 'Server error'], 500);
}
```

## Documentation

When adding features, suggest appropriate documentation updates:
- Feature changes → update `doc/` files
- Breaking changes → update `CHANGELOG.md` with migration guidance
- New extension points → add examples to relevant doc files

## Key Files to Reference

- `src/Application.php` — Request/response orchestrator
- `src/Core/Controller.php` — Base controller class
- `src/Core/Middleware.php` — Middleware base class
- `doc/Routing.md` — Route file specification
- `doc/Configuration.md` — Configuration system
- `CHANGELOG.md` — Breaking changes and migration guides
- `composer.json` — PSR-4 mapping and dependencies
