# AI Instructions and Comprehensive Developer Docs Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create multi-system AI instructions and reorganized, comprehensive developer documentation that serves both framework users and contributors.

**Architecture:** Modular approach with system-specific AI instructions in their expected locations (.claude/, .github/), reorganized documentation with clear sections (Getting Started, User Guide, Best Practices, Recipes, Contributor Guide), cross-referenced guides, and updated navigation hubs.

**Tech Stack:** Markdown documentation, SPIN Framework PHP code examples, PSR standards reference.

---

## Chunk 1: AI Instructions Setup

### Task 1: Create Claude Instructions

**Files:**
- Create: `.claude/claude-instructions.md`

- [ ] **Step 1: Write Claude instructions file**

```markdown
# AI Instructions for Claude — SPIN Framework

Claude Code and Claude API should understand and work effectively with the SPIN Framework. Use these instructions when developing, extending, or debugging SPIN applications and the framework itself.

## Framework Overview

**SPIN Framework** is a lightweight PHP 8+ web framework for building web applications and REST APIs. It emphasizes:
- JSON-based routing (routes never defined in PHP code)
- Middleware pipeline (global-before → group-before → controller → group-after → global-after)
- PSR compliance (PSR-3, PSR-7, PSR-11, PSR-15, PSR-16, PSR-17)
- Minimal overhead with maximum flexibility

## Project Structure

```
src/
├── Application.php        # Main orchestrator
├── Core/                  # Config, Controller, Middleware, RouteGroup, Logger, CacheManager, etc.
├── Cache/Adapters/        # APCu, Redis, File-based cache adapters
├── Database/Drivers/Pdo/  # MySQL, PostgreSQL, SQLite, CockroachDB, Firebird, ODBC
├── Helpers/               # Cipher, JWT, EWT, UUID, ArrayToXml, Hash
├── Factories/Http/        # PSR-17 HTTP message factories
├── Classes/               # RequestIdClass
└── Exceptions/            # Framework exception classes

tests/                     # PHPUnit test suite (mirrors src/ structure)
doc/                       # Comprehensive markdown documentation
.claude/commands/          # CLI commands for Claude Code
.github/copilot-instructions.md  # GitHub Copilot guidance
```

## Key Architectural Decisions

### 1. JSON Routing
Routes are **never** defined in PHP code. All routes go in JSON configuration files with `common`/`groups`/`routes` structure:

```json
{
  "common": {
    "before": ["\\App\\Middlewares\\RequestIdMiddleware"],
    "after": ["\\App\\Middlewares\\ResponseLogMiddleware"]
  },
  "groups": [
    {
      "name": "API",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\CorsMiddleware"],
      "routes": [
        {
          "methods": ["GET"],
          "path": "/users/{id}",
          "handler": "\\App\\Controllers\\UserController"
        }
      ]
    }
  ]
}
```

**Why:** Separates routing concerns from code, makes routes discoverable and visualizable, enables route compilation and optimization.

### 2. Middleware Pipeline
The middleware chain is strictly ordered:
1. Global "before" middleware
2. Route group "before" middleware
3. Controller handler method
4. Route group "after" middleware
5. Global "after" middleware

Returning `false` from `Middleware::handle()` **short-circuits** the entire chain and skips the controller.

**Why:** Clear, predictable execution order; middleware can make routing decisions; after-middleware always runs for response manipulation.

### 3. PSR Compliance
- **PSR-3 (Logger):** Use `Spin\Core\Logger` or global `logger()` helper
- **PSR-7 (HTTP Messages):** Use `Guzzle` for requests/responses
- **PSR-11 (Container):** League Container for dependency injection via `Spin\Core\ConnectionManager`
- **PSR-15 (Middleware):** Middleware extends `Spin\Core\Middleware`, not PSR-15 RequestHandlerInterface
- **PSR-16 (Cache):** Use `Spin\Cache\CacheManager`, adapt to PSR-16 for cache operations
- **PSR-17 (HTTP Factories):** `Spin\Factories\Http\*` for creating HTTP messages

### 4. Global Helpers
Instead of reimplementing plumbing, use:
- `app()` - Get Application instance
- `config(string $key)` - Retrieve configuration
- `getRequest()` / `getResponse()` - Access HTTP messages
- `response(string $body, int $code = 200)` - Create response
- `responseJson(array $data, int $code = 200)` - Create JSON response
- `logger()` - Get Logger instance
- `cache()` - Get Cache instance
- `env(string $key)` - Get environment variable

**Why:** Avoids boilerplate, ensures consistent access patterns, allows framework to manage lifecycle.

## Coding Conventions

**Always follow:**

1. **Declare strict types** at the top of every file:
   ```php
   declare(strict_types=1);
   ```

2. **Use explicit type hints** (no `mixed` unless unavoidable):
   ```php
   public function handleGET(array $args): ResponseInterface
   public function processRequest(string $path, int $limit = 10): array
   ```

3. **Follow PSR-4 namespace mapping** (Spin\Core\Foo → src/Core/Foo.php)

4. **Add docblocks** to all public methods and class-level `@var` annotations:
   ```php
   /**
    * Validate user input against schema
    *
    * @param array $data User-submitted data
    * @param string $schema Name of schema to validate against
    * @return bool True if valid, false otherwise
    */
   public function validate(array $data, string $schema): bool
   ```

5. **Test alongside features** - tests mirror src/ structure. When adding Controller, also add Controller test.

## Extending the Framework

### Adding a Controller

Controllers extend `Spin\Core\Controller`. Implement HTTP method handlers:

```php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $userId = (int)($args['id'] ?? 0);
        $user = $this->getUser($userId);
        return responseJson($user);
    }

    public function handlePOST(array $args): ResponseInterface
    {
        $data = json_decode((string)getRequest()->getBody(), true);
        // Validate and save
        return responseJson(['id' => $newId], 201);
    }

    private function getUser(int $id): array
    {
        // Implementation
    }
}
```

### Adding Middleware

Middleware extends `Spin\Core\Middleware` with two required methods:

```php
declare(strict_types=1);
namespace App\Middlewares;

use Spin\Core\Middleware;

class AuthMiddleware extends Middleware
{
    /**
     * Initialize middleware (setup, read config)
     * Called once per route group
     */
    public function initialize(array $args): bool
    {
        $this->secret = config('application.secret');
        return true; // false aborts initialization
    }

    /**
     * Per-request logic
     * Return false to short-circuit the pipeline
     */
    public function handle(array $args): bool
    {
        $token = getRequest()->getHeaderLine('Authorization');
        if (!$this->validateToken($token)) {
            responseJson(['error' => 'Unauthorized'], 401);
            return false; // Short-circuits pipeline
        }
        return true;
    }
}
```

### Adding a Cache Adapter

Extend `Spin\Cache\Adapters\AbstractCacheAdapter`:

```php
declare(strict_types=1);
namespace Spin\Cache\Adapters;

class MyAdapter extends AbstractCacheAdapter
{
    public function get(string $key, mixed $default = null): mixed
    {
        // Return cached value or default
    }

    public function set(string $key, mixed $value, int|null $ttl = null): bool
    {
        // Store value with optional TTL
    }

    public function delete(string $key): bool
    {
        // Remove value
    }

    public function clear(): bool
    {
        // Clear all
    }

    public function has(string $key): bool
    {
        // Check existence
    }
}
```

Register in config:
```json
{
  "cache": {
    "driver": "myAdapter",
    "adapters": {
      "myAdapter": "\\Spin\\Cache\\Adapters\\MyAdapter"
    }
  }
}
```

### Adding a Database Driver

Extend PDO base class under `src/Database/Drivers/Pdo/`:

```php
declare(strict_types=1);
namespace Spin\Database\Drivers\Pdo;

class MyDatabaseDriver extends PdoDriver
{
    protected string $driverName = 'my-db';

    public function connect(): void
    {
        // Custom connection logic
        parent::connect();
    }
}
```

Register in config:
```json
{
  "database": {
    "drivers": {
      "mydb": "\\Spin\\Database\\Drivers\\Pdo\\MyDatabaseDriver"
    }
  }
}
```

## Testing Patterns

**Test structure mirrors src/ structure:**
```
tests/
├── Unit/
│   └── Cache/
│       └── Adapters/
│           └── MyAdapterTest.php
├── Integration/
│   └── MiddlewareTest.php
└── Feature/
    └── UserControllerTest.php
```

**Use PHPUnit test structure:**

```php
declare(strict_types=1);
namespace Tests\Unit\Cache\Adapters;

use PHPUnit\Framework\TestCase;
use Spin\Cache\Adapters\MyAdapter;

class MyAdapterTest extends TestCase
{
    private MyAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new MyAdapter();
    }

    public function testSetAndGet(): void
    {
        $this->adapter->set('key', 'value');
        $this->assertEquals('value', $this->adapter->get('key'));
    }

    public function testTtlExpiration(): void
    {
        $this->adapter->set('key', 'value', 1); // 1 second TTL
        sleep(2);
        $this->assertNull($this->adapter->get('key'));
    }
}
```

**Run tests:**
```bash
# Windows
.\phpunit.cmd

# Linux/macOS
./vendor/bin/phpunit

# Specific test
./vendor/bin/phpunit tests/Unit/Cache/Adapters/MyAdapterTest.php
```

## Common Tasks & Solutions

### Accessing Request Data
```php
$request = getRequest();
$body = json_decode((string)$request->getBody(), true);
$header = $request->getHeaderLine('Authorization');
$query = $request->getQueryParams();
```

### Creating Responses
```php
// HTML response
return response('<h1>Hello</h1>');

// JSON response
return responseJson(['status' => 'ok'], 200);

// Custom response
$response = response('Custom body', 404);
return $response->withHeader('X-Custom', 'value');
```

### Database Access
```php
$db = app()->getDatabase();
$result = $db->query("SELECT * FROM users WHERE id = ?", [$userId]);
$users = $result->fetchAll();
```

### Caching
```php
$cache = cache();
$cached = $cache->get('users:' . $userId);
if ($cached === null) {
    $data = expensiveOperation();
    $cache->set('users:' . $userId, $data, 3600); // 1 hour TTL
}
```

### Logging
```php
$log = logger();
$log->info('User logged in', ['userId' => $userId]);
$log->error('Database connection failed', ['error' => $e->getMessage()]);
```

## Anti-Patterns to Avoid

❌ **Don't define routes in PHP**
```php
// WRONG - never do this
$app->get('/users', 'UserController');
```
Routes go in JSON configuration files.

❌ **Don't use mixed types** unless absolutely necessary
```php
// WRONG
public function process(mixed $data): mixed

// RIGHT
public function process(array $data): array
```

❌ **Don't skip declare(strict_types=1)**
Every file must have it.

❌ **Don't return from middleware handle() without proper response**
```php
// WRONG
public function handle(array $args): bool {
    if ($invalid) {
        return false; // No response set!
    }
    return true;
}

// RIGHT
public function handle(array $args): bool {
    if ($invalid) {
        responseJson(['error' => 'Invalid'], 400);
        return false;
    }
    return true;
}
```

❌ **Don't forget to extend tests when changing core**
If you modify a class, update its test too.

## When to Ask for Clarification

Ask the user for clarification when:
- The task requires breaking PSR compliance
- The feature requires modifying the JSON routing structure
- The implementation affects multiple middleware or global helpers
- The change is a breaking API change (modify Spin\Core\* public signatures)
- The documentation scope expands beyond the spec

## Key Files to Know

- `src/Application.php` — Main application orchestrator and request lifecycle
- `src/Core/Controller.php` — Base controller class
- `src/Core/Middleware.php` — Base middleware class
- `src/Core/Config.php` — Configuration management with macro expansion
- `src/Core/Logger.php` — Logging facade (PSR-3)
- `src/Cache/CacheManager.php` — Cache abstraction
- `.claude/commands/test.md` — Claude Code test command
- `.claude/commands/release.md` — Claude Code release command
- `CLAUDE.md` — SPIN Framework guidelines for AI

## Related Repository

Companion skeleton project at `c:\Data\Repos\Celarius\github\spin-skeleton` demonstrates SPIN usage patterns and app structure.
```

- [ ] **Step 2: Verify file created and has correct markdown formatting**

Check: `cat .claude/claude-instructions.md | head -50`

Expected: File starts with `# AI Instructions for Claude — SPIN Framework` and contains framework guidance.

- [ ] **Step 3: Commit**

```bash
git add .claude/claude-instructions.md
git commit -m "docs: add comprehensive Claude AI instructions for SPIN Framework"
```

---

### Task 2: Create GitHub Copilot Instructions

**Files:**
- Create: `.github/copilot-instructions.md`

- [ ] **Step 1: Write Copilot instructions file**

```markdown
# GitHub Copilot Instructions — SPIN Framework

These instructions guide GitHub Copilot when suggesting code completions in SPIN Framework projects.

## Project Structure

```
src/
├── Application.php          # Main orchestrator
├── Core/                    # Core framework classes
│   ├── Controller.php       # Base controller
│   ├── Middleware.php       # Base middleware
│   ├── Config.php           # Configuration management
│   ├── Logger.php           # Logging facade
│   ├── CacheManager.php     # Cache abstraction
│   └── ...
├── Cache/Adapters/          # Cache implementations
├── Database/Drivers/Pdo/    # Database drivers
├── Helpers/                 # Utility functions
└── Exceptions/              # Exception classes

tests/                       # PHPUnit tests (mirrors src/)
doc/                         # Documentation
.claude/commands/            # CLI commands
.github/copilot-instructions.md  # This file
```

## Naming Conventions

- **Namespaces:** `Spin\*` for framework core, `App\*` for application code
- **Controllers:** PascalCase + `Controller` suffix (e.g., `UserController`, `OrderController`)
- **Middleware:** PascalCase + `Middleware` suffix (e.g., `AuthMiddleware`, `CorsMiddleware`)
- **Cache Adapters:** PascalCase + `Adapter` suffix in `src/Cache/Adapters/` (e.g., `RedisAdapter`, `FileAdapter`)
- **Database Drivers:** DatabaseName + `Driver` in `src/Database/Drivers/Pdo/`
- **Interfaces:** PascalCase with `Interface` suffix (e.g., `StorageInterface`)
- **Traits:** PascalCase with `Trait` suffix (e.g., `TimestampTrait`)
- **Abstract Classes:** `Abstract` prefix (e.g., `AbstractCacheAdapter`)
- **Exceptions:** PascalCase + `Exception` suffix (e.g., `ConfigException`, `DatabaseException`)

## Code Style Guidelines

### 1. Declare Strict Types
Every PHP file must start with:
```php
declare(strict_types=1);
```

### 2. Type Hints (PSR-12)
Always use explicit type hints. Never use `mixed` unless absolutely necessary:

```php
// Good
public function getUser(int $id): User
public function processArray(array $data): array
public function validateEmail(string $email): bool

// Avoid
public function getData(mixed $id): mixed
```

### 3. Docblocks
Add docblocks to all public methods and properties:

```php
/**
 * Process user data and store in database
 *
 * @param array $data User-submitted data
 * @param string $validator Schema name for validation
 * @return int ID of created user
 * @throws ValidationException If data invalid
 */
public function createUser(array $data, string $validator): int
```

### 4. Method Spacing
- One blank line between methods
- No blank lines within method logic (unless for logical grouping)

### 5. Control Structures
```php
// Good
if ($condition) {
    doSomething();
} else {
    doOtherThing();
}

// Use early returns
if (!$valid) {
    return false;
}
doWork();
```

## Common Patterns to Suggest

### Controller Handler Methods

```php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class ItemController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $id = (int)($args['id'] ?? 0);
        // GET logic
        return responseJson($data);
    }

    public function handlePOST(array $args): ResponseInterface
    {
        $body = json_decode((string)getRequest()->getBody(), true);
        // POST logic
        return responseJson($result, 201);
    }
}
```

### Middleware Methods

```php
declare(strict_types=1);
namespace App\Middlewares;

use Spin\Core\Middleware;

class CustomMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        // Setup
        return true;
    }

    public function handle(array $args): bool
    {
        // Per-request logic
        return true; // or false to short-circuit
    }
}
```

### Route Configuration Structure

```json
{
  "common": {
    "before": ["\\App\\Middlewares\\BeforeMiddleware"],
    "after": ["\\App\\Middlewares\\AfterMiddleware"]
  },
  "groups": [
    {
      "name": "API",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\ApiAuthMiddleware"],
      "routes": [
        {
          "methods": ["GET", "POST"],
          "path": "/items/{id}",
          "handler": "\\App\\Controllers\\ItemController"
        }
      ]
    }
  ]
}
```

### Helper Usage

```php
// Configuration
$dbConfig = config('database.default');
$appName = config('application.name');

// HTTP
$request = getRequest();
$response = response('Body text', 200);
$jsonResp = responseJson(['key' => 'value'], 200);

// Logging
logger()->info('Processing order', ['orderId' => $id]);

// Caching
$cached = cache()->get('key');
cache()->set('key', $value, 3600);

// Environment
$dbHost = env('DB_HOST');
```

## Testing Patterns

Suggest test structure mirroring src/:

```php
declare(strict_types=1);
namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;

class UserControllerTest extends TestCase
{
    private UserController $controller;

    protected function setUp(): void
    {
        $this->controller = new UserController();
    }

    public function testHandleGET(): void
    {
        // Test implementation
        $this->assertTrue(true);
    }
}
```

## Framework Dependencies to Reference

- **HTTP Messages:** `Psr\Http\Message\ResponseInterface`, `Psr\Http\Message\RequestInterface`
- **Controllers:** `Spin\Core\Controller`
- **Middleware:** `Spin\Core\Middleware`
- **Exceptions:** `Spin\Exceptions\*`
- **Helpers:** `config()`, `response()`, `responseJson()`, `logger()`, `cache()`, `env()`
- **Cache:** `Spin\Cache\CacheManager`, `Spin\Cache\Adapters\*`
- **Database:** `Spin\Database\ConnectionManager`
- **Utilities:** `Spin\Helpers\JWT`, `Spin\Helpers\UUID`, `Spin\Helpers\Hash`

## Anti-Patterns to Avoid Suggesting

❌ **Defining routes in PHP**
```php
// Never suggest this
$app->post('/users', 'UserController@store');
```
Routes go in JSON configuration.

❌ **Using mixed type hints unnecessarily**
```php
// Avoid
public function process(mixed $data): mixed

// Prefer
public function process(array $data): array
```

❌ **Missing strict_types declaration**
Every file must have `declare(strict_types=1);`

❌ **Missing docblocks on public methods**
All public APIs must have documentation.

❌ **Ignoring middleware return value**
```php
// Wrong - should check return and handle false
public function handle(array $args): bool {
    doSomething();
    return true;
}

// Right - explicitly handle both cases
public function handle(array $args): bool {
    if (!$this->authorize()) {
        responseJson(['error' => 'Forbidden'], 403);
        return false;
    }
    return true;
}
```

## File Locations

| Task | Location |
|------|----------|
| Create controller | `src/app/Controllers/YourController.php` |
| Create middleware | `src/app/Middlewares/YourMiddleware.php` |
| Create route config | `src/app/Config/routes.json` |
| Create test | `tests/Unit/Path/ToTest.php` |
| Add helper function | `src/app/Globals.php` |
| Add database driver | `src/Database/Drivers/Pdo/YourDriver.php` |
| Add cache adapter | `src/Cache/Adapters/YourAdapter.php` |

## Configuration Reference

Routes configuration goes in `src/app/Config/routes-{env}.json`:

```json
{
  "common": {
    "before": [],
    "after": []
  },
  "groups": [
    {
      "name": "GroupName",
      "prefix": "/api/v1",
      "before": ["\\Fully\\Qualified\\MiddlewareClass"],
      "routes": [
        {
          "methods": ["GET", "POST"],
          "path": "/resource/{id}",
          "handler": "\\Fully\\Qualified\\ControllerClass"
        }
      ]
    }
  ]
}
```
```

- [ ] **Step 2: Verify file created**

Check: `cat .github/copilot-instructions.md | head -30`

Expected: File starts with `# GitHub Copilot Instructions — SPIN Framework`

- [ ] **Step 3: Commit**

```bash
git add .github/copilot-instructions.md
git commit -m "docs: add GitHub Copilot instructions for SPIN Framework"
```

---

### Task 3: Create Generic LLM Instructions

**Files:**
- Create: `.claude/llm-instructions.md`

- [ ] **Step 1: Write generic LLM instructions file**

```markdown
# AI Instructions for LLMs — SPIN Framework

Generic instructions for any Large Language Model (Claude, ChatGPT, Gemini, etc.) working with SPIN Framework.

## What is SPIN Framework?

SPIN is a lightweight, high-performance PHP 8+ web framework for building web applications and REST APIs. It prioritizes:
- **Simplicity:** Minimal boilerplate, straightforward patterns
- **Performance:** Optimized for speed with lazy loading and smart caching
- **Standards:** Full PSR compliance (PSR-3, PSR-7, PSR-11, PSR-15, PSR-16, PSR-17)
- **Flexibility:** Easy to extend and customize

**Current Version:** 0.0.35 (pre-1.0, actively developed)
**Package Name:** `celarius/spin-framework`
**License:** MIT

## Core Concepts

### 1. JSON-Based Routing
Routes are never defined in PHP code. Instead, they're configured in JSON files with three key sections:

- **common:** Global middleware applied to all routes
- **groups:** Logical groupings of related routes (e.g., `/api/v1`, `/admin`)
- **routes:** Individual route definitions with HTTP methods, paths, and handlers

```json
{
  "common": {
    "before": ["\\App\\Middlewares\\RequestIdMiddleware"],
    "after": ["\\App\\Middlewares\\ResponseLogMiddleware"]
  },
  "groups": [
    {
      "name": "API v1",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\AuthMiddleware"],
      "routes": [
        {
          "methods": ["GET"],
          "path": "/items/{id}",
          "handler": "\\App\\Controllers\\ItemController"
        }
      ]
    }
  ]
}
```

**Benefit:** Routes are discoverable, can be visualized/documented separately from code, easier to debug.

### 2. Middleware Pipeline
Requests flow through a strict pipeline:

```
Global Before Middleware
    ↓
Route Group Before Middleware
    ↓
Controller Handler
    ↓
Route Group After Middleware
    ↓
Global After Middleware
```

Middleware can return `false` to **short-circuit** the pipeline, skipping everything after it.

### 3. Request-Response Cycle
A request arrives → routed to appropriate handler → response returned → can be modified by after-middleware.

### 4. Dependency Injection
SPIN uses PSR-11 container (League Container) for dependency resolution. Access via:
- `app()` — Application instance
- `config('key')` — Configuration values
- `getRequest()` / `getResponse()` — HTTP messages
- `logger()` — Logger instance
- `cache()` — Cache instance

## Architecture Overview

```
Application.php
    ├─ Request Lifecycle Manager
    ├─ Router (nikic/fast-route)
    ├─ Middleware Pipeline
    └─ Response Handler

Core/
    ├─ Controller        # Base class for request handlers
    ├─ Middleware        # Base class for pipeline components
    ├─ Config            # JSON config with ${env:VAR} macro expansion
    ├─ Logger            # PSR-3 logging facade
    ├─ CacheManager      # Cache abstraction (PSR-16 compatible)
    └─ ConnectionManager # DI container wrapper

Cache/Adapters/
    ├─ APCuAdapter       # In-memory caching
    ├─ RedisAdapter      # Distributed caching
    └─ FileAdapter       # Persistent caching

Database/Drivers/Pdo/
    ├─ MySqlDriver
    ├─ PostgresDriver
    ├─ SqliteDriver
    └─ (others)

Helpers/
    ├─ JWT               # Token generation/validation
    ├─ Hash              # Password hashing
    ├─ UUID              # Unique identifier generation
    └─ (others)
```

## Key Framework Files and Purposes

| File | Purpose |
|------|---------|
| `src/Application.php` | Main orchestrator, manages request lifecycle and response |
| `src/Core/Controller.php` | Base class for all controllers |
| `src/Core/Middleware.php` | Base class for middleware components |
| `src/Core/Config.php` | Configuration management with macro expansion |
| `src/Core/Logger.php` | Logging abstraction (PSR-3) |
| `src/Cache/CacheManager.php` | Cache abstraction (PSR-16 style) |
| `tests/` | PHPUnit tests (mirrors src/ structure) |
| `doc/` | Comprehensive markdown documentation |

## Coding Standards and Conventions

### PHP Version
- **Minimum:** PHP 8.0
- **Recommended:** PHP 8.1+
- Use modern PHP features (match expressions, named arguments, etc.)

### File Structure
```php
declare(strict_types=1);

namespace App\Controllers; // PSR-4 mapping: Spin\ → src/, App\ → app/

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    // Class implementation
}
```

### Naming Conventions
- **Classes:** PascalCase
- **Methods:** camelCase
- **Constants:** SCREAMING_SNAKE_CASE
- **Properties:** $camelCase
- **Files:** Match class name exactly (PSR-4)

### Type Hints
```php
// ✓ Good
public function getUserById(int $id): User|null
public function processArray(array $data): array
public function validateEmail(string $email): bool

// ✗ Avoid
public function getData(mixed $id): mixed
```

### Docblock Format
```php
/**
 * Brief description of what this does
 *
 * More detailed explanation if needed.
 * Can span multiple lines.
 *
 * @param string $email User email address
 * @param int $ttl Time-to-live in seconds (optional, default 3600)
 * @return bool True if email valid, false otherwise
 * @throws InvalidEmailException If email format invalid
 */
public function validateEmail(string $email, int $ttl = 3600): bool
```

## How to Extend the Framework

### Create a Custom Controller
```php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class BookController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $bookId = (int)($args['id'] ?? 0);
        $book = $this->getBook($bookId);
        return responseJson($book);
    }

    public function handlePOST(array $args): ResponseInterface
    {
        $data = json_decode((string)getRequest()->getBody(), true);
        $bookId = $this->createBook($data);
        return responseJson(['id' => $bookId], 201);
    }

    private function getBook(int $id): array
    {
        // Fetch from database
        return ['id' => $id, 'title' => 'Example Book'];
    }

    private function createBook(array $data): int
    {
        // Save to database
        return 1;
    }
}
```

### Create Custom Middleware
```php
declare(strict_types=1);
namespace App\Middlewares;

use Spin\Core\Middleware;

class RateLimitMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        // Called once when route is configured
        $this->limit = config('ratelimit.requests_per_minute');
        return true;
    }

    public function handle(array $args): bool
    {
        // Called for each request
        $clientId = getRequest()->getHeaderLine('X-Client-ID');
        $requests = $this->getRequestCount($clientId);

        if ($requests > $this->limit) {
            responseJson(['error' => 'Rate limit exceeded'], 429);
            return false; // Short-circuit pipeline
        }

        return true; // Continue to next middleware/controller
    }

    private function getRequestCount(string $clientId): int
    {
        // Check against cache/database
        return 0;
    }
}
```

### Create Custom Cache Adapter
```php
declare(strict_types=1);
namespace Spin\Cache\Adapters;

class CustomAdapter extends AbstractCacheAdapter
{
    public function get(string $key, mixed $default = null): mixed
    {
        // Retrieve from storage
        return null; // return $default if not found
    }

    public function set(string $key, mixed $value, int|null $ttl = null): bool
    {
        // Store value with optional TTL
        return true;
    }

    public function delete(string $key): bool
    {
        // Remove from storage
        return true;
    }

    public function clear(): bool
    {
        // Clear all entries
        return true;
    }

    public function has(string $key): bool
    {
        // Check if key exists
        return false;
    }
}
```

## Common Pitfalls and Solutions

### Pitfall 1: Defining Routes in PHP
```php
// ✗ WRONG - Don't do this
$app->get('/users', 'UserController');
```
**Solution:** Use JSON configuration in `config/routes-{env}.json` or route.json files.

### Pitfall 2: Using mixed Type Hints Liberally
```php
// ✗ Avoid
public function process(mixed $data): mixed

// ✓ Better
public function process(array $data): array
```
**Why:** Strong typing prevents bugs and improves IDE autocomplete.

### Pitfall 3: Forgetting declare(strict_types=1)
```php
// ✗ Missing at top of file
namespace App\Controllers;

// ✓ Correct
declare(strict_types=1);
namespace App\Controllers;
```

### Pitfall 4: Not Returning Response in Middleware
```php
// ✗ Wrong - short-circuits but no response!
public function handle(array $args): bool
{
    if ($invalid) {
        return false;
    }
    return true;
}

// ✓ Correct - sets response before short-circuiting
public function handle(array $args): bool
{
    if ($invalid) {
        responseJson(['error' => 'Invalid'], 400);
        return false;
    }
    return true;
}
```

### Pitfall 5: Ignoring After-Middleware
After-middleware runs even if controller sets a response. Use it for:
- Response modification (add headers, transform body)
- Logging and monitoring
- Cleanup tasks

```php
public function handle(array $args): bool
{
    // This runs AFTER controller, good for response decoration
    $response = getResponse();
    $response = $response->withHeader('X-Processed', 'true');
    return true;
}
```

## Testing Best Practices

- **Test structure mirrors source:** Controller → tests/Unit/Controllers/ControllerTest.php
- **Use PHPUnit:** Framework uses PHPUnit for testing
- **Test setup/teardown:** Initialize dependencies in setUp()
- **Assert specific behaviors:** Test one thing per test method
- **Name tests clearly:** `testValidInputReturnsUser` not `testUser`

```php
declare(strict_types=1);
namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;

class UserControllerTest extends TestCase
{
    private UserController $controller;

    protected function setUp(): void
    {
        $this->controller = new UserController();
    }

    public function testHandleGETWithValidId(): void
    {
        $result = $this->controller->handleGET(['id' => 1]);
        $this->assertNotNull($result);
    }
}
```

## Global Helper Functions

Instead of creating boilerplate:

```php
// Configuration
$value = config('section.key');

// HTTP Responses
response($body, $statusCode);
responseJson(['key' => 'value'], $statusCode);

// Request/Response Access
$request = getRequest();
$response = getResponse();

// Logging
logger()->info('message', $context);

// Caching
cache()->get('key');
cache()->set('key', $value, $ttl);

// Environment Variables
env('VAR_NAME');

// Application
$app = app();
```

## References and Further Reading

- Framework Documentation: See `doc/` directory in SPIN Framework repository
- PSR Standards: https://www.php-fig.org/
- Composer: https://getcomposer.org/
- GitHub Repository: https://github.com/Celarius/spin-framework

## Summary

SPIN Framework emphasizes:
1. **Clear separation:** JSON routes, PHP logic, middleware pipeline
2. **Standards compliance:** PSR compliance for maximum compatibility
3. **Minimalism:** Only what's needed, nothing more
4. **Extensibility:** Easy to add controllers, middleware, adapters, drivers
5. **Testability:** Structure supports unit and integration testing

When working with SPIN, ask yourself:
- Does this follow the framework's patterns?
- Have I added tests?
- Is the code typed properly?
- Does this belong in a controller, middleware, or helper?
```

- [ ] **Step 2: Verify file created**

Check: `head -20 .claude/llm-instructions.md`

- [ ] **Step 3: Commit**

```bash
git add .claude/llm-instructions.md
git commit -m "docs: add generic LLM instructions for SPIN Framework"
```

---

## Chunk 2: Documentation Structure and Navigation

### Task 4: Create Documentation Directory Structure

**Files:**
- Create directories for reorganized documentation

- [ ] **Step 1: Create directory structure**

```bash
mkdir -p doc/Getting-Started
mkdir -p doc/User-Guide
mkdir -p doc/Best-Practices
mkdir -p doc/Recipes
mkdir -p doc/Contributor-Guide
mkdir -p doc/Reference
```

- [ ] **Step 2: Move existing documentation files to User-Guide**

```bash
# Move all existing doc files to User-Guide (no content changes)
mv doc/Configuration.md doc/User-Guide/
mv doc/Routing.md doc/User-Guide/
mv doc/Middleware.md doc/User-Guide/
mv doc/Databases.md doc/User-Guide/
mv doc/Cache.md doc/User-Guide/
mv doc/Uploaded-files.md doc/User-Guide/
mv doc/Storage-folders.md doc/User-Guide/
mv doc/Helpers.md doc/User-Guide/
mv doc/Security.md doc/User-Guide/
mv doc/Testing.md doc/User-Guide/
```

- [ ] **Step 3: Verify structure**

Run: `find doc -type f -name "*.md" | sort`

Expected: All .md files organized into subdirectories.

---

### Task 5: Create Documentation Navigation Hub

**Files:**
- Create: `doc/README.md`

- [ ] **Step 1: Write documentation README**

```markdown
# SPIN Framework Documentation

Welcome to comprehensive documentation for the SPIN Framework. This guide will help you find exactly what you need, whether you're building applications with SPIN or contributing to the framework itself.

## Quick Navigation

**New to SPIN?** Start with [Getting Started](#getting-started)
**Building an App?** Jump to [User Guide](#user-guide)
**Need best practices?** Check out [Best Practices](#best-practices)
**Looking for solutions?** See [Recipes](#recipes)
**Contributing to SPIN?** Visit [Contributor Guide](#contributor-guide)

---

## Getting Started

Onboarding guides for new SPIN developers.

- **[Quick Start](Getting-Started/Quick-Start.md)** (5 min)
  Start a SPIN project in 5 minutes with your first route

- **[Project Structure](Getting-Started/Project-Structure.md)**
  Understand the layout and where everything goes

- **[Core Concepts](Getting-Started/Core-Concepts.md)**
  Learn JSON routing, middleware pipeline, and PSR compliance

- **[Your First App](Getting-Started/Your-First-App.md)** (Tutorial)
  Build a complete CRUD app from scratch

**Reading Order for Beginners:**
1. Quick Start
2. Project Structure
3. Core Concepts
4. Your First App
5. Browse User Guide for specific features

---

## User Guide

Feature documentation for building applications with SPIN.

### Configuration & Routing
- **[Configuration](User-Guide/Configuration.md)** — JSON-based app configuration with macro expansion
- **[Routing](User-Guide/Routing.md)** — JSON route definitions, groups, middleware

### Controllers & Middleware
- **[Controllers](User-Guide/Controllers.md)** — Request handlers, HTTP method handling, responses
- **[Middleware](User-Guide/Middleware.md)** — Pipeline, before/after logic, short-circuiting

### Data & Storage
- **[Databases](User-Guide/Databases.md)** — Database connections, queries, drivers
- **[Cache](User-Guide/Cache.md)** — Caching adapters, TTL, cache invalidation
- **[File Uploads](User-Guide/Uploaded-files.md)** — Secure file handling, validation
- **[Storage Folders](User-Guide/Storage-folders.md)** — Application storage organization

### Utilities & Security
- **[Helpers](User-Guide/Helpers.md)** — Global functions, utilities, common operations
- **[Security](User-Guide/Security.md)** — Best practices, CSRF, SQL injection prevention
- **[Testing](User-Guide/Testing.md)** — Unit tests, integration tests, PHPUnit setup

---

## Best Practices

Patterns, approaches, and design guidance for building robust SPIN applications.

- **[Application Design](Best-Practices/Application-Design.md)** — Structuring apps for maintainability
- **[Error Handling](Best-Practices/Error-Handling.md)** — Exception patterns, error responses
- **[Performance Optimization](Best-Practices/Performance-Optimization.md)** — Caching, query optimization
- **[Database Patterns](Best-Practices/Database-Patterns.md)** — Connection management, transactions
- **[Caching Strategies](Best-Practices/Caching-Strategies.md)** — When/what/how to cache
- **[Testing Patterns](Best-Practices/Testing-Patterns.md)** — Structuring tests, mocking, coverage

---

## Recipes

Step-by-step solutions for common development tasks.

- **[Authentication](Recipes/Authentication.md)** — JWT, session-based auth, middleware patterns
- **[File Uploads](Recipes/File-Uploads.md)** — Upload validation, security, storage
- **[Rate Limiting](Recipes/Rate-Limiting.md)** — Implementing request throttling
- **[CORS Handling](Recipes/CORS-Handling.md)** — Cross-origin requests, preflight
- **[API Versioning](Recipes/API-Versioning.md)** — Version strategies, backward compatibility
- **[Deployment](Recipes/Deployment.md)** — Environment setup, health checks, migrations

---

## Contributor Guide

Documentation for those extending and maintaining the SPIN Framework.

- **[Getting Started](Contributor-Guide/Getting-Started.md)** — Dev environment, testing, code review
- **[Architecture Overview](Contributor-Guide/Architecture-Overview.md)** — Core components, request lifecycle
- **[Extension Points](Contributor-Guide/Extension-Points.md)** — Creating adapters, drivers, middleware
- **[Testing Guide](Contributor-Guide/Testing-Guide.md)** — Writing framework tests, coverage
- **[Code Standards](Contributor-Guide/Code-Standards.md)** — PSR compliance, typing, docblocks
- **[Submitting Changes](Contributor-Guide/Submitting-Changes.md)** — PR process, changelog, releases

---

## Reference

Technical reference and API documentation.

- **[API Reference](Reference/API-Reference.md)** — Core classes, methods, signatures

---

## Topical Index

### By Task

| Task | Document |
|------|----------|
| Set up a new project | [Quick Start](Getting-Started/Quick-Start.md) |
| Understand framework structure | [Project Structure](Getting-Started/Project-Structure.md) |
| Define routes | [Routing](User-Guide/Routing.md) |
| Create a controller | [Controllers](User-Guide/Controllers.md) |
| Add middleware | [Middleware](User-Guide/Middleware.md) |
| Connect to database | [Databases](User-Guide/Databases.md) |
| Implement caching | [Cache](User-Guide/Cache.md) |
| Handle file uploads | [File Uploads](Recipes/File-Uploads.md) |
| Authenticate users | [Authentication](Recipes/Authentication.md) |
| Limit requests | [Rate Limiting](Recipes/Rate-Limiting.md) |
| Handle errors | [Error Handling](Best-Practices/Error-Handling.md) |
| Write tests | [Testing Patterns](Best-Practices/Testing-Patterns.md) |
| Deploy application | [Deployment](Recipes/Deployment.md) |

### By Audience

| Audience | Start Here |
|----------|-----------|
| New users | [Quick Start](Getting-Started/Quick-Start.md) → [Your First App](Getting-Started/Your-First-App.md) |
| App developers | [User Guide](#user-guide) → [Best Practices](#best-practices) |
| Framework contributors | [Contributor Guide](#contributor-guide) |
| Looking for solutions | [Recipes](#recipes) |

---

## API Documentation

Generated from source code docblocks. See [API Reference](Reference/API-Reference.md).

---

## Need Help?

- **Documentation Issue?** Report at GitHub Issues
- **Question?** Ask in GitHub Discussions
- **Feature Request?** Open a GitHub Issue

---

## Related Projects

- **[SPIN Skeleton](https://github.com/Celarius/spin-skeleton)** — Official starter template
- **[SPIN Framework](https://github.com/Celarius/spin-framework)** — Main repository

---

**Last Updated:** 2026-03-15
```

- [ ] **Step 2: Verify file created**

Check: `head -30 doc/README.md`

---

## Chunk 3: Getting Started Guides (≤1000 lines)

> **Plan continues...** Due to length constraints, the remainder (Tasks 6-40) will follow in a separate continuation. Each Getting Started, Best Practices, Recipes, and Contributor Guide document follows the same pattern:
>
> **For each guide:**
> - Step 1: Write the markdown file with comprehensive content
> - Step 2: Verify file created and readable
> - Step 3: Commit with appropriate message
>
> See tasks below for complete file list.

### Task 6: Quick-Start.md
- Create: `doc/Getting-Started/Quick-Start.md` (~350 words)
  - 5-minute setup
  - Create first route
  - Run dev server
  - Test endpoint

### Task 7: Project-Structure.md
- Create: `doc/Getting-Started/Project-Structure.md` (~400 words)
  - Framework structure (src/)
  - Application structure (app/)
  - Configuration files
  - Where files go

### Task 8: Core-Concepts.md
- Create: `doc/Getting-Started/Core-Concepts.md` (~400 words)
  - JSON routing explanation
  - Middleware pipeline
  - Request-response cycle
  - Global helpers

### Task 9: Your-First-App.md
- Create: `doc/Getting-Started/Your-First-App.md` (~500 words)
  - Step-by-step tutorial
  - Create task CRUD endpoints
  - Add database layer
  - Test endpoints

---

## Chunk 4: Best Practices Guides (≤1000 lines)

### Task 10: Application-Design.md
- Create: `doc/Best-Practices/Application-Design.md` (~450 words)
  - Separation of concerns
  - Layer design (controller, service, repository)
  - Dependency injection
  - Organization patterns

### Task 11: Error-Handling.md
- Create: `doc/Best-Practices/Error-Handling.md` (~400 words)
  - Exception hierarchy
  - Custom exceptions
  - Error responses
  - Logging errors

### Task 12: Performance-Optimization.md
- Create: `doc/Best-Practices/Performance-Optimization.md` (~450 words)
  - Caching strategies
  - Query optimization
  - Middleware efficiency
  - Monitoring performance

### Task 13: Database-Patterns.md
- Create: `doc/Best-Practices/Database-Patterns.md` (~450 words)
  - Connection management
  - Query patterns
  - Transaction handling
  - Migration strategies

### Task 14: Caching-Strategies.md
- Create: `doc/Best-Practices/Caching-Strategies.md` (~450 words)
  - When to cache
  - Cache invalidation
  - Adapter selection
  - TTL strategies

### Task 15: Testing-Patterns.md
- Create: `doc/Best-Practices/Testing-Patterns.md` (~450 words)
  - Unit vs integration tests
  - Mocking strategies
  - Test organization
  - Coverage targets

---

## Chunk 5: Recipes (≤1000 lines)

### Task 16: Authentication.md
- Create: `doc/Recipes/Authentication.md` (~500 words)
  - JWT implementation
  - Session-based auth
  - Auth middleware
  - Token refresh

### Task 17: File-Uploads.md
- Create: `doc/Recipes/File-Uploads.md` (~500 words)
  - Upload validation
  - Security checks
  - Storage integration
  - File organization

### Task 18: Rate-Limiting.md
- Create: `doc/Recipes/Rate-Limiting.md` (~400 words)
  - Middleware implementation
  - Redis-based limiting
  - Per-user tracking
  - Error responses

### Task 19: CORS-Handling.md
- Create: `doc/Recipes/CORS-Handling.md` (~400 words)
  - CORS headers
  - Preflight requests
  - CORS middleware
  - Configuration

### Task 20: API-Versioning.md
- Create: `doc/Recipes/API-Versioning.md` (~450 words)
  - URL-based versioning
  - Header-based versioning
  - Backward compatibility
  - Migration strategies

### Task 21: Deployment.md
- Create: `doc/Recipes/Deployment.md` (~500 words)
  - Environment configuration
  - Database migrations
  - Health checks
  - Production checklist

---

## Chunk 6: Contributor Guide (≤1000 lines)

### Task 22: Contributor-Getting-Started.md
- Create: `doc/Contributor-Guide/Getting-Started.md` (~400 words)
  - Dev environment setup
  - Running tests
  - Code review process
  - Git workflow

### Task 23: Architecture-Overview.md
- Create: `doc/Contributor-Guide/Architecture-Overview.md` (~500 words)
  - Core components
  - Request lifecycle
  - DI container usage
  - Extension mechanism

### Task 24: Extension-Points.md
- Create: `doc/Contributor-Guide/Extension-Points.md` (~500 words)
  - Creating cache adapters
  - Creating database drivers
  - Custom middleware
  - Custom helpers

### Task 25: Testing-Guide.md
- Create: `doc/Contributor-Guide/Testing-Guide.md` (~400 words)
  - Framework test structure
  - Writing tests
  - CI/CD integration
  - Coverage requirements

### Task 26: Code-Standards.md
- Create: `doc/Contributor-Guide/Code-Standards.md` (~450 words)
  - PSR-4, PSR-12 compliance
  - Type hints requirements
  - Docblock standards
  - Naming conventions

### Task 27: Submitting-Changes.md
- Create: `doc/Contributor-Guide/Submitting-Changes.md` (~400 words)
  - PR process
  - Changelog updates
  - Breaking changes
  - Review checklist

---

## Chunk 7: Reference and Final Updates (≤1000 lines)

### Task 28: API-Reference.md
- Create: `doc/Reference/API-Reference.md` (~1500-2000 words)
  - Core class listing
  - Key methods and signatures
  - Exception types
  - Helper functions

### Task 29: Update CLAUDE.md

**Files:**
- Modify: `CLAUDE.md`

- [ ] **Step 1: Add reference to AI instructions**

In CLAUDE.md, after the "Key Architectural Decisions" section, add:

```markdown
## AI Instructions

Comprehensive AI instructions for working with SPIN Framework are available in:
- **Claude:** [.claude/claude-instructions.md](.claude/claude-instructions.md)
- **GitHub Copilot:** [.github/copilot-instructions.md](.github/copilot-instructions.md)
- **Other LLMs:** [.claude/llm-instructions.md](.claude/llm-instructions.md)

These documents provide framework-specific guidance for code generation, extension patterns, and best practices.
```

- [ ] **Step 2: Commit**

```bash
git add CLAUDE.md
git commit -m "docs: add references to AI instructions in CLAUDE.md"
```

---

### Task 30: Update README.md

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Update documentation link section**

Replace the existing documentation section in README.md with:

```markdown
## 📚 Documentation

Comprehensive documentation is available in the `doc/` directory, organized by audience and use case.

### For Application Developers
- **Getting Started:** [Quick Start Guide](doc/Getting-Started/Quick-Start.md) — Build your first SPIN app in 5 minutes
- **User Guide:** [Core documentation](doc/User-Guide/) for all framework features
- **Best Practices:** [Design patterns and strategies](doc/Best-Practices/) for robust applications
- **Recipes:** [Common solutions](doc/Recipes/) for typical tasks

### For Framework Contributors
- **Contributor Guide:** [Extending and maintaining SPIN](doc/Contributor-Guide/)
- **Architecture Overview:** [Internal structure and request lifecycle](doc/Contributor-Guide/Architecture-Overview.md)

### Quick Links
| Task | Link |
|------|------|
| Set up new project | [Quick Start](doc/Getting-Started/Quick-Start.md) |
| Define routes | [Routing Guide](doc/User-Guide/Routing.md) |
| Add authentication | [Auth Recipe](doc/Recipes/Authentication.md) |
| Deploy application | [Deployment Guide](doc/Recipes/Deployment.md) |

**Full documentation index:** [doc/README.md](doc/README.md)
```

- [ ] **Step 2: Commit**

```bash
git add README.md
git commit -m "docs: update README with link to comprehensive documentation structure"
```

---

## Final Verification

### Task 31: Verify All Files Created

- [ ] **Step 1: Check all documentation files exist**

```bash
ls -la doc/README.md
ls -la doc/Getting-Started/
ls -la doc/User-Guide/
ls -la doc/Best-Practices/
ls -la doc/Recipes/
ls -la doc/Contributor-Guide/
ls -la doc/Reference/
ls -la .claude/claude-instructions.md
ls -la .github/copilot-instructions.md
ls -la .claude/llm-instructions.md
```

- [ ] **Step 2: Verify AI instructions files are readable**

```bash
head -10 .claude/claude-instructions.md
head -10 .github/copilot-instructions.md
head -10 .claude/llm-instructions.md
```

- [ ] **Step 3: Check doc/README.md navigation**

```bash
grep -c "markdown link" doc/README.md
```

Expected: Navigation file contains proper markdown links.

---

## Execution Summary

This plan creates:
- ✅ 3 AI instruction files (Claude, Copilot, generic LLM)
- ✅ 1 documentation navigation hub
- ✅ 4 Getting Started guides
- ✅ 6 Best Practices guides
- ✅ 6 Recipe guides
- ✅ 6 Contributor guides
- ✅ 1 API Reference guide
- ✅ Updated CLAUDE.md and README.md
- ✅ Directory structure reorganization

**Total new/modified files:** ~35 files
**Total documentation:** ~15,000+ words
**Commits:** ~10 (one per major section)

All files follow markdown best practices with:
- Clear headings and structure
- Code examples where applicable
- Cross-references to related guides
- Consistent formatting

---

**Ready to execute?**
