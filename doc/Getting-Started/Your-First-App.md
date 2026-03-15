# Your First App — Building a Task Management API

This comprehensive tutorial walks you through building a complete Task/TodoList CRUD API from scratch. You'll create controllers, define routes, connect to a database, and test all endpoints.

**Estimated time:** 30-45 minutes

## Project Setup

Create and initialize your project:

```bash
mkdir task-api
cd task-api
composer init

# Configure composer.json with basic info
# When asked about PSR-4 autoload, enter: App -> src/app

composer require celarius/spin-framework
```

Create the directory structure:

```bash
mkdir -p src/app/Controllers
mkdir -p src/app/Config
mkdir -p src/app/Middlewares
mkdir -p src/app/Services
mkdir -p public
mkdir -p storage/logs
```

## Step 1: Create Your First Controller

Create `src/app/Controllers/TaskController.php`:

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use App\Services\TaskService;

class TaskController extends Controller
{
    /**
     * Get all tasks or filtered tasks
     */
    public function handleGET(array $args): ResponseInterface
    {
        $page = (int) (queryParam('page', '1'));
        $limit = (int) (queryParam('limit', '10'));

        $tasks = TaskService::getAll($page, $limit);

        return responseJson([
            'success' => true,
            'data' => $tasks,
            'pagination' => [
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }

    /**
     * Create a new task
     */
    public function handlePOST(array $args): ResponseInterface
    {
        $body = json_decode((string) getRequest()->getBody(), true);

        if (empty($body['title'])) {
            return responseJson([
                'success' => false,
                'error' => 'Title is required'
            ], 400);
        }

        try {
            $task = TaskService::create([
                'title' => $body['title'],
                'description' => $body['description'] ?? null,
                'status' => $body['status'] ?? 'pending'
            ]);

            return responseJson([
                'success' => true,
                'data' => $task
            ], 201);
        } catch (\Exception $e) {
            logger()->error('Failed to create task', ['error' => $e->getMessage()]);
            return responseJson([
                'success' => false,
                'error' => 'Failed to create task'
            ], 500);
        }
    }
}
```

Create `src/app/Controllers/SingleTaskController.php`:

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use App\Services\TaskService;

class SingleTaskController extends Controller
{
    /**
     * Get a specific task by ID
     */
    public function handleGET(array $args): ResponseInterface
    {
        $id = (int) ($args['id'] ?? 0);

        $task = TaskService::getById($id);

        if (!$task) {
            return responseJson([
                'success' => false,
                'error' => 'Task not found'
            ], 404);
        }

        return responseJson([
            'success' => true,
            'data' => $task
        ]);
    }

    /**
     * Update a task
     */
    public function handlePUT(array $args): ResponseInterface
    {
        $id = (int) ($args['id'] ?? 0);
        $body = json_decode((string) getRequest()->getBody(), true);

        $task = TaskService::getById($id);
        if (!$task) {
            return responseJson([
                'success' => false,
                'error' => 'Task not found'
            ], 404);
        }

        try {
            $updated = TaskService::update($id, $body);

            return responseJson([
                'success' => true,
                'data' => $updated
            ]);
        } catch (\Exception $e) {
            logger()->error('Failed to update task', ['error' => $e->getMessage()]);
            return responseJson([
                'success' => false,
                'error' => 'Failed to update task'
            ], 500);
        }
    }

    /**
     * Delete a task
     */
    public function handleDELETE(array $args): ResponseInterface
    {
        $id = (int) ($args['id'] ?? 0);

        $task = TaskService::getById($id);
        if (!$task) {
            return responseJson([
                'success' => false,
                'error' => 'Task not found'
            ], 404);
        }

        try {
            TaskService::delete($id);

            return responseJson([
                'success' => true,
                'data' => ['id' => $id, 'deleted' => true]
            ]);
        } catch (\Exception $e) {
            logger()->error('Failed to delete task', ['error' => $e->getMessage()]);
            return responseJson([
                'success' => false,
                'error' => 'Failed to delete task'
            ], 500);
        }
    }
}
```

## Step 2: Create a Service Layer

Create `src/app/Services/TaskService.php`:

```php
<?php declare(strict_types=1);

namespace App\Services;

use Spin\Helpers\UUID;

class TaskService
{
    // In-memory storage (replace with database)
    private static array $tasks = [];
    private static int $nextId = 1;

    public static function getAll(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        return array_slice(self::$tasks, $offset, $limit);
    }

    public static function getById(int $id): ?array
    {
        return self::$tasks[$id] ?? null;
    }

    public static function create(array $data): array
    {
        $id = self::$nextId++;
        $task = [
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        self::$tasks[$id] = $task;
        logger()->info('Task created', ['id' => $id, 'title' => $data['title']]);

        return $task;
    }

    public static function update(int $id, array $data): array
    {
        if (!isset(self::$tasks[$id])) {
            throw new \Exception('Task not found');
        }

        $task = self::$tasks[$id];

        if (isset($data['title'])) {
            $task['title'] = $data['title'];
        }
        if (isset($data['description'])) {
            $task['description'] = $data['description'];
        }
        if (isset($data['status'])) {
            $task['status'] = $data['status'];
        }

        $task['updated_at'] = date('Y-m-d H:i:s');
        self::$tasks[$id] = $task;

        logger()->info('Task updated', ['id' => $id]);

        return $task;
    }

    public static function delete(int $id): bool
    {
        if (!isset(self::$tasks[$id])) {
            throw new \Exception('Task not found');
        }

        unset(self::$tasks[$id]);
        logger()->info('Task deleted', ['id' => $id]);

        return true;
    }
}
```

## Step 3: Define Your Routes

Create `src/app/Config/routes.json`:

```json
{
  "common": {
    "before": [],
    "after": []
  },
  "groups": [
    {
      "name": "Task API",
      "notes": "CRUD endpoints for task management",
      "prefix": "/api/v1",
      "before": [],
      "routes": [
        {
          "methods": ["GET", "POST"],
          "path": "/tasks",
          "handler": "\\App\\Controllers\\TaskController"
        },
        {
          "methods": ["GET", "PUT", "DELETE"],
          "path": "/tasks/{id}",
          "handler": "\\App\\Controllers\\SingleTaskController"
        }
      ],
      "after": []
    }
  ],
  "errors": {}
}
```

## Step 4: Bootstrap Your Application

Create `public/index.php`:

```php
<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Spin\Application;

// Determine environment
$env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev';

// Create and run application
$app = new Application(
    projectPath: dirname(__DIR__),
    configFile: 'routes.json',
    environment: $env
);

$app->run();
```

Create a simple `src/app/Globals.php` (optional, for registering custom helpers):

```php
<?php declare(strict_types=1);

namespace App;

// Register any application-specific globals here
// Example: Register custom helpers, load bootstrap code, etc.
```

## Step 5: Create Configuration

Create `src/app/Config/config.json`:

```json
{
  "app": {
    "name": "Task API",
    "version": "1.0.0",
    "timezone": "UTC"
  },
  "logging": {
    "default": "monolog",
    "level": "INFO"
  }
}
```

Create `src/app/Config/config-dev.json`:

```json
{
  "debug": true,
  "logging": {
    "level": "DEBUG",
    "path": "storage/logs/app.log"
  }
}
```

## Step 6: Start the Development Server

```bash
php -S localhost:8000 -t public/
```

You'll see:
```
Development Server (http://localhost:8000)
Listening on http://localhost:8000
```

## Step 7: Test Your API

### Create a Task (POST)

```bash
curl -X POST http://localhost:8000/api/v1/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Learn SPIN Framework",
    "description": "Understand routing and middleware",
    "status": "in_progress"
  }'
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Learn SPIN Framework",
    "description": "Understand routing and middleware",
    "status": "in_progress",
    "created_at": "2026-03-15 14:45:30",
    "updated_at": "2026-03-15 14:45:30"
  }
}
```

### Get All Tasks (GET)

```bash
curl http://localhost:8000/api/v1/tasks
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Learn SPIN Framework",
      "description": "Understand routing and middleware",
      "status": "in_progress",
      "created_at": "2026-03-15 14:45:30",
      "updated_at": "2026-03-15 14:45:30"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 10
  }
}
```

### Get Single Task (GET)

```bash
curl http://localhost:8000/api/v1/tasks/1
```

### Update Task (PUT)

```bash
curl -X PUT http://localhost:8000/api/v1/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed"
  }'
```

### Delete Task (DELETE)

```bash
curl -X DELETE http://localhost:8000/api/v1/tasks/1
```

## Step 8: Use Postman for Testing

Import this Postman collection:

1. Open Postman
2. Click **Import** → **Raw text**
3. Paste this:

```json
{
  "info": {
    "name": "Task API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Create Task",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\"title\": \"New Task\", \"description\": \"Task description\", \"status\": \"pending\"}"
        },
        "url": {
          "raw": "http://localhost:8000/api/v1/tasks",
          "protocol": "http",
          "host": ["localhost"],
          "port": "8000",
          "path": ["api", "v1", "tasks"]
        }
      }
    },
    {
      "name": "Get All Tasks",
      "request": {
        "method": "GET",
        "url": {
          "raw": "http://localhost:8000/api/v1/tasks",
          "protocol": "http",
          "host": ["localhost"],
          "port": "8000",
          "path": ["api", "v1", "tasks"]
        }
      }
    }
  ]
}
```

## Step 9: Add Logging and Error Handling

Update `src/app/Controllers/TaskController.php` to use logging:

```php
public function handleGET(array $args): ResponseInterface
{
    $page = (int) (queryParam('page', '1'));
    $limit = (int) (queryParam('limit', '10'));

    logger()->debug('Fetching tasks', ['page' => $page, 'limit' => $limit]);

    $tasks = TaskService::getAll($page, $limit);

    logger()->info('Tasks fetched', ['count' => count($tasks)]);

    return responseJson([
        'success' => true,
        'data' => $tasks,
        'pagination' => ['page' => $page, 'limit' => $limit]
    ]);
}
```

## Next Steps

Now that you have a working API, consider:

1. **Connect to a Real Database**
   - Read [User-Guide/Databases.md](../User-Guide/Databases.md)
   - Update `TaskService` to use PDO queries instead of in-memory arrays

2. **Add Authentication**
   - Create an `AuthBeforeMiddleware`
   - Implement JWT token validation
   - Protect private endpoints
   - See [User-Guide/Security.md](../User-Guide/Security.md)

3. **Improve Error Handling**
   - Create error controller (`Error4xxController`, `Error5xxController`)
   - Return consistent error responses
   - Register in routes' `"errors"` section

4. **Add Caching**
   - Cache task lists with `cache()->set()`
   - Invalidate on updates
   - Read [User-Guide/Cache.md](../User-Guide/Cache.md)

5. **Write Tests**
   - Create unit tests for `TaskService`
   - Create integration tests for endpoints
   - Read [User-Guide/Testing.md](../User-Guide/Testing.md)

6. **Deploy to Production**
   - Set up environment configs for production
   - Use Docker containerization
   - Read [Best-Practices/](../Best-Practices/) deployment guides

## Key Takeaways

✓ Controllers handle HTTP requests and return responses
✓ Services encapsulate business logic
✓ Routes are defined in JSON for clarity
✓ Global helpers (`logger()`, `cache()`, `responseJson()`) make coding easier
✓ Middleware can be added for cross-cutting concerns

## See Also

- [Quick-Start.md](Quick-Start.md) — 5-minute setup
- [Core-Concepts.md](Core-Concepts.md) — How everything works together
- [User-Guide/Routing.md](../User-Guide/Routing.md) — Advanced routing patterns
- [User-Guide/Databases.md](../User-Guide/Databases.md) — Database integration
- [User-Guide/Security.md](../User-Guide/Security.md) — Authentication and authorization
