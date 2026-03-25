# SPIN Framework API Reference

Complete API reference for core SPIN Framework classes, methods, and global helper functions.

## Table of Contents

1. [Core Classes](#core-classes)
2. [Helper Functions](#helper-functions)
3. [Exception Types](#exception-types)
4. [Manager Classes](#manager-classes)

---

## Core Classes

### Application

The main application orchestrator managing the request/response lifecycle, routing, middleware execution, and dependency injection.

**Namespace:** `Spin\Application`

**Key Methods:**

| Method | Signature | Description |
|--------|-----------|-------------|
| `__construct()` | `public function __construct()` | Initialize the application instance |
| `run()` | `public function run(): void` | Execute the application lifecycle and handle HTTP request |
| `getEnvironment()` | `public function getEnvironment(): string` | Get current environment name |
| `getBasePath()` | `public function getBasePath(): string` | Get application base path |
| `getAppPath()` | `public function getAppPath(): string` | Get app folder path |
| `getConfig()` | `public function getConfig(): ?Config` | Get Configuration object |
| `getLogger()` | `public function getLogger(): ?Logger` | Get Logger instance |
| `getCache()` | `public function getCache(): ?AbstractCacheAdapterInterface` | Get Cache adapter |
| `getConnectionManager()` | `public function getConnectionManager(): ?ConnectionManager` | Get database connection manager |
| `getContainer()` | `public function getContainer(): ?ContainerInterface` | Get PSR-11 service container |
| `getProperty()` | `public function getProperty(string $property): mixed` | Get application property by name |

**Usage Example:**

```php
$app = app(); // Get global application instance
$env = $app->getEnvironment(); // e.g., "development"
$config = $app->getConfig(); // Access configuration
```

---

### Controller

Base controller class providing HTTP method handlers and convenient access to framework services.

**Namespace:** `Spin\Core\Controller`

**Key Methods:**

| Method | Signature | Description |
|--------|-----------|-------------|
| `initialize()` | `public function initialize(array $args): void` | Called after controller instantiation, before middleware |
| `handle()` | `public function handle(array $args): ResponseInterface` | Main dispatcher; routes to HTTP method handlers |
| `handleGET()` | `public function handleGET(array $args): ResponseInterface` | Handle GET requests |
| `handlePOST()` | `public function handlePOST(array $args): ResponseInterface` | Handle POST requests |
| `handlePUT()` | `public function handlePUT(array $args): ResponseInterface` | Handle PUT requests |
| `handlePATCH()` | `public function handlePATCH(array $args): ResponseInterface` | Handle PATCH requests |
| `handleDELETE()` | `public function handleDELETE(array $args): ResponseInterface` | Handle DELETE requests |
| `handleHEAD()` | `public function handleHEAD(array $args): ResponseInterface` | Handle HEAD requests |
| `handleOPTIONS()` | `public function handleOPTIONS(array $args): ResponseInterface` | Handle OPTIONS requests |

**Usage Example:**

```php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    public function initialize(array $args): void
    {
        // Setup controller state
    }

    public function handleGET(array $args): ResponseInterface
    {
        $userId = $args['id'] ?? null;
        return responseJson(['user_id' => $userId]);
    }

    public function handlePOST(array $args): ResponseInterface
    {
        $data = json_decode(getRequest()->getBody(), true);
        // Process creation
        return responseJson(['status' => 'created'], 201);
    }
}
```

---

### Middleware

Base middleware class for request/response pipeline processing.

**Namespace:** `Spin\Core\Middleware`

**Key Methods:**

| Method | Signature | Description |
|--------|-----------|-------------|
| `initialize()` | `public function initialize(array $args): bool` | Setup phase; return false to fail |
| `handle()` | `public function handle(array $args): bool` | Per-request processing; false short-circuits pipeline |

**Execution Order:**
Global-before → Group-before → Controller → Group-after → Global-after

**Usage Example:**

```php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class AuthMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        // Load auth config, setup
        return true;
    }

    public function handle(array $args): bool
    {
        $token = getRequest()->getHeaderLine('Authorization');
        if (!$this->validateToken($token)) {
            responseJson(['error' => 'Unauthorized'], 401);
            return false; // Short-circuit the pipeline
        }
        return true; // Continue
    }
}
```

---

### Config

Application configuration management with JSON-based files and dot-notation accessors.

**Namespace:** `Spin\Core\Config`

**Key Methods:**

| Method | Signature | Description |
|--------|-----------|-------------|
| `__construct()` | `public function __construct(string $appPath, string $environment)` | Load config-{environment}.json |
| `get()` | `public function get(string $key, mixed $default = null): mixed` | Get config value by dot-notation key |
| `set()` | `public function set(string $key, mixed $value): self` | Set config value by key |
| `has()` | `public function has(string $key): bool` | Check if key exists |
| `load()` | `public function load(string $filename): self` | Load and merge JSON config file |
| `all()` | `public function all(): array` | Get all configuration as array |
| `clear()` | `public function clear(): self` | Clear all values |

**Dot Notation Examples:**

```php
$timeout = config('session.timeout');        // Get session timeout
$secret = config('application.secret');      // Get app secret
config('cache.driver', 'redis');            // Set cache driver
```

---

### Logger

PSR-3 compliant logger extending Monolog.

**Namespace:** `Spin\Core\Logger`

**Key Methods (PSR-3):**

| Method | Signature | Description |
|--------|-----------|-------------|
| `emergency()` | `public function emergency(string \$message, array \$context = []): void` | Emergency level |
| `alert()` | `public function alert(string \$message, array \$context = []): void` | Alert level |
| `critical()` | `public function critical(string \$message, array \$context = []): void` | Critical level |
| `error()` | `public function error(string \$message, array \$context = []): void` | Error level |
| `warning()` | `public function warning(string \$message, array \$context = []): void` | Warning level |
| `notice()` | `public function notice(string \$message, array \$context = []): void` | Notice level |
| `info()` | `public function info(string \$message, array \$context = []): void` | Informational level |
| `debug()` | `public function debug(string \$message, array \$context = []): void` | Debug level |
| `log()` | `public function log(\$level, string \$message, array \$context = []): void` | Log at level |

**Usage Example:**

```php
$log = logger(); // Get global logger
$log->info('User login', ['user_id' => 123]);
$log->error('Database error', ['error' => 'Connection failed']);
logger()->debug('Debug info', ['payload' => $data]);
```

---

### CacheManager

PSR-16 compatible cache adapter manager supporting multiple backends.

**Namespace:** `Spin\Core\CacheManager`

**Key Methods:**

| Method | Signature | Description |
|--------|-----------|-------------|
| `__construct()` | `public function __construct(array \$options)` | Initialize with config options |
| `get()` | `public function get(string \$key, mixed \$default = null): mixed` | Get cached value |
| `set()` | `public function set(string \$key, mixed \$value, \$ttl = null): bool` | Set cache with optional TTL |
| `delete()` | `public function delete(string \$key): bool` | Delete cache entry |
| `clear()` | `public function clear(): bool` | Clear all cache |
| `getMultiple()` | `public function getMultiple(iterable \$keys, mixed \$default = null): iterable` | Get multiple values |
| `setMultiple()` | `public function setMultiple(iterable \$values, \$ttl = null): bool` | Set multiple values |
| `deleteMultiple()` | `public function deleteMultiple(iterable \$keys): bool` | Delete multiple values |
| `has()` | `public function has(string \$key): bool` | Check if key exists |

**Supported Adapters:**

- **APCu** - In-memory cache (requires PHP APCu extension)
- **Redis** - Distributed cache (via Predis)
- **File** - File-based cache (local storage)

**Usage Example:**

```php
$cache = cache(); // Get global cache instance
$value = $cache->get('user_123'); // Get value
$cache->set('user_123', ['name' => 'John'], 3600); // 1-hour TTL
$cache->has('user_123'); // Check existence
$cache->delete('user_123'); // Delete entry
$cache->clear(); // Clear all
```

---

### ConnectionManager

Database connection pool manager supporting multiple drivers and databases.

**Namespace:** `Spin\Core\ConnectionManager`

**Key Methods:**

| Method | Signature | Description |
|--------|-----------|-------------|
| `__construct()` | `public function __construct(array \$connections)` | Initialize with connection configs |
| `connect()` | `public function connect(string \$name = 'default'): ?PdoConnection` | Get/create connection |
| `disconnect()` | `public function disconnect(string \$name = null): bool` | Close connection |
| `disconnectAll()` | `public function disconnectAll(): bool` | Close all connections |
| `getConnection()` | `public function getConnection(string \$name = 'default'): ?PdoConnection` | Get active connection |

**Supported Drivers:**

- MySQL
- PostgreSQL
- SQLite
- CockroachDB
- Firebird
- ODBC

**Usage Example:**

```php
$db = db(); // Get default database connection
$db = db('secondary'); // Get named connection
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([123]);
$user = $stmt->fetch();
```

---

## Helper Functions

Global helper functions available throughout the application for convenient access to framework services.

### env()

Get environment variable with type coercion support.

**Signature:** `function env(string $var, mixed $default = null): mixed`

```php
$debug = env('DEBUG', false); // Coerces "true"/"false" to boolean
$port = env('PORT', 8000);
$custom = env('CUSTOM_VAR', 'default');
```

---

### app()

Get the global Application instance or a specific property.

**Signature:** `function app(?string $property = null): mixed`

```php
$app = app(); // Get Application instance
$env = app('environment'); // Get specific property
```

---

### config()

Get/set configuration values using dot-notation keys.

**Signature:** `function config(?string $key = null, mixed $value = null): mixed`

```php
$value = config('application.secret');
config('cache.driver', 'redis');
$allConfig = config(); // Get Config object
```

---

### container()

Get or set values in the PSR-11 dependency injection container.

**Signature:** `function container(?string $id = null, mixed $value = null): mixed`

```php
$value = container('MyService');
container('MyKey', 'value');
container()->add('MyClass', 'App\\Custom\\MyClass');
```

---

### logger()

Get the global Logger instance (PSR-3 compliant).

**Signature:** `function logger(): Logger`

```php
logger()->info('Application started');
logger()->error('Error occurred', ['exception' => $e]);
```

---

### db()

Get a database connection from the ConnectionManager.

**Signature:** `function db(?string $name = 'default'): ?PdoConnection`

```php
$db = db();           // Default connection
$db = db('secondary'); // Named connection
```

---

### cache()

Get the global Cache adapter instance.

**Signature:** `function cache(): ?AbstractCacheAdapterInterface`

```php
$value = cache()->get('key');
cache()->set('key', $value, 3600);
```

---

### getRequest()

Get the current PSR-7 ServerRequest instance.

**Signature:** `function getRequest(): ?RequestInterface`

```php
$method = getRequest()->getMethod(); // GET, POST, etc.
$body = getRequest()->getBody();
$header = getRequest()->getHeaderLine('Authorization');
```

---

### getResponse()

Get the current PSR-7 Response instance.

**Signature:** `function getResponse(): ?ResponseInterface`

```php
$response = getResponse();
$response = $response->withStatus(200);
```

---

### response()

Create a text/HTML response.

**Signature:** `function response(string $body, int $status = 200, array $headers = []): ResponseInterface`

```php
return response('<h1>Hello</h1>', 200, ['Content-Type' => 'text/html']);
```

---

### responseJson()

Create a JSON response.

**Signature:** `function responseJson(mixed $data, int $status = 200, array $headers = []): ResponseInterface`

```php
return responseJson(['status' => 'success', 'data' => $user], 200);
return responseJson(['error' => 'Not found'], 404);
```

---

### responseXml()

Create an XML response.

**Signature:** `function responseXml(array $data, int $status = 200, array $headers = []): ResponseInterface`

```php
return responseXml(['user' => ['id' => 1, 'name' => 'John']], 200);
```

---

### responseHtml()

Create an HTML response with template rendering.

**Signature:** `function responseHtml(string $html, int $status = 200, array $headers = []): ResponseInterface`

```php
return responseHtml('<div>Content</div>', 200);
```

---

### responseFile()

Create a file download response.

**Signature:** `function responseFile(string $filepath, ?string $filename = null, int $status = 200): ResponseInterface`

```php
return responseFile('/path/to/file.pdf', 'download.pdf');
```

---

### redirect()

Create a redirect response.

**Signature:** `function redirect(string $location, int $status = 302): ResponseInterface`

```php
return redirect('/login');
return redirect('https://example.com', 301);
```

---

### queryParam()

Get a single query parameter from the request.

**Signature:** `function queryParam(string $key, mixed $default = null): mixed`

```php
$page = queryParam('page', 1);
$search = queryParam('q');
```

---

### queryParams()

Get all query parameters as an array.

**Signature:** `function queryParams(): array`

```php
$params = queryParams(); // ['page' => '1', 'sort' => 'name']
```

---

### postParam()

Get a single POST parameter from the request body.

**Signature:** `function postParam(string $key, mixed $default = null): mixed`

```php
$username = postParam('username');
$age = postParam('age', 0);
```

---

### postParams()

Get all POST parameters as an array.

**Signature:** `function postParams(): array`

```php
$params = postParams(); // ['username' => 'john', 'password' => '...']
```

---

### cookieParam()

Get a single cookie value.

**Signature:** `function cookieParam(string $key, mixed $default = null): mixed`

```php
$sessionId = cookieParam('SESSIONID');
```

---

### cookieParams()

Get all cookies as an array.

**Signature:** `function cookieParams(): array`

```php
$cookies = cookieParams(); // ['SESSIONID' => '...', ...]
```

---

### cookie()

Set a response cookie.

**Signature:** `function cookie(string $name, string $value, array $options = []): bool`

```php
cookie('session_id', '12345', ['expires' => time() + 3600]);
cookie('remember', 'user123', ['path' => '/', 'secure' => true]);
```

---

### getClientIp()

Get the client's IP address from the request.

**Signature:** `function getClientIp(): ?string`

```php
$ip = getClientIp(); // e.g., "192.168.1.1"
```

---

### generateRefId()

Generate a unique reference ID for tracking.

**Signature:** `function generateRefId(): string`

```php
$refId = generateRefId(); // e.g., "REF-20250315-abc123"
```

---

## Exception Types

Framework exception classes for error handling.

### SpinException

Base exception class for all SPIN Framework exceptions.

**Namespace:** `Spin\Exceptions\SpinException`

```php
try {
    // Framework operation
} catch (SpinException $e) {
    logger()->error('SPIN error', ['message' => $e->getMessage()]);
}
```

### ConfigException

Thrown when configuration errors occur.

**Namespace:** `Spin\Exceptions\ConfigException`

```php
try {
    $value = config('missing.key');
} catch (ConfigException $e) {
    // Handle config error
}
```

### DatabaseException

Thrown for database operation errors.

**Namespace:** `Spin\Exceptions\DatabaseException`

```php
try {
    $db->prepare('SELECT * FROM users')->execute();
} catch (DatabaseException $e) {
    logger()->error('Database error', ['message' => $e->getMessage()]);
}
```

### CacheException

Thrown for cache adapter errors.

**Namespace:** `Spin\Exceptions\CacheException`

```php
try {
    cache()->set('key', $value);
} catch (CacheException $e) {
    // Handle cache error
}
```

### MiddlewareException

Thrown when middleware processing fails.

**Namespace:** `Spin\Exceptions\MiddlewareException`

```php
try {
    // Middleware initialization
} catch (MiddlewareException $e) {
    // Handle middleware error
}
```

---

## Manager Classes

### CacheManager

Manages cache adapter instances and operations.

**Configuration Example:**

```json
{
  "cache": {
    "default": "apcu",
    "adapters": {
      "apcu": {
        "driver": "apcu"
      },
      "redis": {
        "driver": "redis",
        "host": "localhost",
        "port": 6379,
        "ttl": 3600
      },
      "file": {
        "driver": "file",
        "path": "storage/cache"
      }
    }
  }
}
```

---

### ConnectionManager

Manages database connections with pooling and lazy loading.

**Configuration Example:**

```json
{
  "database": {
    "default": "mysql",
    "connections": {
      "mysql": {
        "driver": "mysql",
        "host": "localhost",
        "port": 3306,
        "database": "myapp",
        "username": "${env:DB_USER}",
        "password": "${env:DB_PASSWORD}"
      },
      "postgresql": {
        "driver": "postgresql",
        "host": "localhost",
        "port": 5432,
        "database": "myapp",
        "username": "${env:DB_USER}",
        "password": "${env:DB_PASSWORD}"
      }
    }
  }
}
```

---

## See Also

- [Configuration](../User-Guide/Configuration.md) — JSON-based configuration
- [Routing](../User-Guide/Routing.md) — Route definition and handling
- [Middleware](../User-Guide/Middleware.md) — Middleware pipeline
- [Databases](../User-Guide/Databases.md) — Database operations
- [Cache](../User-Guide/Cache.md) — Caching strategies
- [Helpers](../User-Guide/Helpers.md) — Additional utility functions
