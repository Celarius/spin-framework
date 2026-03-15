# Core Concepts — How SPIN Framework Works

This guide explains the fundamental concepts that power the SPIN Framework: routing, middleware, request-response cycle, and global helpers.

## JSON-Based Routing

Unlike many frameworks where routes are defined in PHP code, SPIN uses JSON configuration. This provides a single source of truth for your API structure.

### How It Works

Define routes in `config/routes-dev.json`:

```json
{
  "groups": [
    {
      "name": "API Routes",
      "prefix": "/api",
      "routes": [
        {
          "methods": ["GET"],
          "path": "/tasks/{id}",
          "handler": "\\App\\Controllers\\TaskController"
        }
      ]
    }
  ]
}
```

When a request arrives for `GET /api/tasks/42`:
1. SPIN matches against route patterns
2. Extracts path parameters (`id` → `42`)
3. Routes to the controller handler

### Route Parameters

Use curly braces for dynamic segments:

```json
{
  "path": "/tasks/{id}",           // Single parameter
  "handler": "\\App\\Controllers\\TaskController"
},
{
  "path": "/projects/{projectId}/tasks/{taskId}",
  "handler": "\\App\\Controllers\\ProjectTaskController"
}
```

In your controller, receive parameters as an array:

```php
public function handleGET(array $args): ResponseInterface
{
    $taskId = $args['id'] ?? null;
    // Use $taskId to fetch and return task
}
```

### Route Methods

Specify HTTP methods handled by a route:

```json
{
  "methods": ["GET"],              // GET only
  "path": "/tasks",
  "handler": "\\App\\Controllers\\TaskController"
},
{
  "methods": ["POST", "PUT"],      // Multiple methods
  "path": "/tasks",
  "handler": "\\App\\Controllers\\TaskController"
},
{
  "methods": [],                   // All methods
  "path": "/",
  "handler": "\\App\\Controllers\\IndexController"
}
```

Your controller implements corresponding methods:

```php
class TaskController extends Controller
{
    public function handleGET(array $args): ResponseInterface { ... }
    public function handlePOST(array $args): ResponseInterface { ... }
    public function handlePUT(array $args): ResponseInterface { ... }
    public function handleDELETE(array $args): ResponseInterface { ... }
}
```

If a method isn't implemented, SPIN returns 405 Method Not Allowed.

## Middleware Pipeline

Middleware processes requests before reaching your controller, and responses before returning to the client. Think of it as layered filtering.

### Pipeline Order

Request flows through middleware in this order:

```
Request
  ↓
Global "before" middleware
  ↓
Route group "before" middleware
  ↓
Controller handler
  ↓
Route group "after" middleware
  ↓
Global "after" middleware
  ↓
Response
```

### Defining Middleware

Middleware extends `Spin\Core\Middleware`:

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class AuthBeforeMiddleware extends Middleware
{
    /**
     * Initialize middleware (runs once)
     */
    public function initialize(array $args): bool
    {
        // Setup code: load config, prepare resources
        return true;  // Proceed to next step
    }

    /**
     * Handle request (runs per request)
     */
    public function handle(array $args): bool
    {
        $request = getRequest();
        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            // Short-circuit: skip controller, go to after middleware
            return false;
        }

        // Continue pipeline
        return true;
    }
}
```

### Registering Middleware

In your routes configuration:

```json
{
  "common": {
    "before": [
      "\\App\\Middlewares\\RequestIdBeforeMiddleware"
    ],
    "after": [
      "\\App\\Middlewares\\ResponseLogAfterMiddleware"
    ]
  },
  "groups": [
    {
      "name": "Protected Routes",
      "before": [
        "\\App\\Middlewares\\AuthBeforeMiddleware"
      ],
      "routes": [
        { "methods": ["GET"], "path": "/profile", "handler": "\\App\\Controllers\\ProfileController" }
      ],
      "after": [
        "\\App\\Middlewares\\AuditLogAfterMiddleware"
      ]
    }
  ]
}
```

### Short-Circuiting

Return `false` from middleware to skip remaining pipeline:

```php
public function handle(array $args): bool
{
    if (!$this->isAuthorized()) {
        return responseJson(['error' => 'Unauthorized'], 401);
    }
    return true;
}
```

When you return false, execution jumps to "after" middleware, skipping the controller.

## Request-Response Cycle

Every HTTP request follows this lifecycle:

### 1. Request Arrives

Browser/client sends HTTP request:
```
GET /api/tasks/42
Host: myapp.com
Authorization: Bearer token123
```

### 2. Route Matching

SPIN matches request path and method against route definitions.

### 3. Middleware Before

Global and group "before" middleware execute in order. Each can:
- Log/audit the request
- Validate authorization
- Modify the request
- Short-circuit by returning false

### 4. Controller Execution

Your controller method handles the request:

```php
public function handleGET(array $args): ResponseInterface
{
    $id = $args['id'];
    $task = TaskService::find($id);

    if (!$task) {
        return responseJson(['error' => 'Not found'], 404);
    }

    return responseJson(['task' => $task]);
}
```

### 5. Middleware After

Group and global "after" middleware execute. Common uses:
- Log response details
- Add headers (CORS, security)
- Measure response time
- Track metrics

### 6. Response Returned

SPIN sends the response back to the client:
```
HTTP/1.1 200 OK
Content-Type: application/json

{"task": {"id": 42, "title": "Learn SPIN"}}
```

## Global Helper Functions

SPIN provides convenient global functions. These are always available in your code.

### Configuration

```php
$value = config('app.name');
$databaseConfig = config('database');
$withDefault = config('feature.flag', false);
```

### Request/Response

```php
$request = getRequest();                      // PSR-7 RequestInterface
$response = getResponse();                    // PSR-7 ResponseInterface
$method = $request->getMethod();
$body = (string) $request->getBody();
```

### Building Responses

```php
// JSON response
responseJson(['key' => 'value'], 200);

// HTML response
response('<html>...</html>', 200);

// Redirect
redirect('/path/to/page', 302);
```

### Request Parameters

```php
$param = getParam('key');              // From route path
$queryParam = queryParam('search');    // From query string (?search=foo)
$postParam = postParam('email');       // From POST body
$header = headerParam('X-Custom');     // From HTTP header
```

### Logging

```php
logger()->info('User logged in', ['userId' => 123]);
logger()->error('Database error', ['error' => $e->getMessage()]);
```

### Caching

```php
$cached = cache()->get('key');
cache()->set('key', $value, 3600);     // 3600 seconds TTL
cache()->delete('key');
```

### Other Helpers

```php
$uuid = generateUuid();                 // Ramsey UUID
$hashed = hashPassword('secret');       // Bcrypt hash
$verified = verifyPassword('secret', $hashed);
$tokenData = jwt()->decode($token);     // JWT decoding
```

### Using Helpers

All helpers work in controllers, middleware, models, and services:

```php
class TaskController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $search = queryParam('search', '');
        $cached = cache()->get("search:{$search}");

        if ($cached) {
            logger()->info('Cache hit', ['search' => $search]);
            return responseJson(json_decode($cached, true));
        }

        $results = TaskService::search($search);
        cache()->set("search:{$search}", json_encode($results), 600);
        logger()->info('Cache miss', ['search' => $search]);

        return responseJson(['results' => $results]);
    }
}
```

## PSR Compliance

SPIN follows PHP Standards Recommendations (PSR):

| PSR | Purpose | Usage |
|-----|---------|-------|
| PSR-3 | Logging | `logger()` returns PSR-3 logger |
| PSR-7 | HTTP messages | `getRequest()`, `getResponse()` |
| PSR-11 | Container/DI | Access via `app()->getContainer()` |
| PSR-16 | Caching | `cache()` implements PSR-16 |
| PSR-17 | HTTP factories | Create requests/responses |

This means SPIN integrates seamlessly with PSR-compatible packages.

### Example: Using a PSR-3 Logger

```php
use Psr\Log\LoggerInterface;

class MyMiddleware extends Middleware
{
    private LoggerInterface $logger;

    public function initialize(array $args): bool
    {
        $this->logger = logger();  // PSR-3 compatible
        return true;
    }

    public function handle(array $args): bool
    {
        $this->logger->info('Request processed');
        return true;
    }
}
```

## Summary

- **Routes** are defined in JSON, mapping paths to controllers
- **Middleware** processes requests before/after controllers, can short-circuit
- **Request-Response Cycle** flows through middleware → controller → middleware
- **Global Helpers** provide convenient access to config, logging, caching, etc.
- **PSR Compliance** ensures compatibility with the PHP ecosystem

## Next Steps

- [Your-First-App.md](Your-First-App.md) — Build a complete application
- [User-Guide/Routing.md](../User-Guide/Routing.md) — Advanced routing patterns
- [User-Guide/Middleware.md](../User-Guide/Middleware.md) — Middleware deep dive
- [User-Guide/Configuration.md](../User-Guide/Configuration.md) — Configuration management
