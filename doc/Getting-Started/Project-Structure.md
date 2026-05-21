# Project Structure — Understanding the Layout

SPIN Framework uses a clear, organized directory structure. This guide explains the framework directories, your application structure, and where to place your code.

## Framework Structure (`src/`)

The SPIN Framework itself lives in the `src/` directory of the composer package. Understanding this helps you know what's available.

```
vendor/celarius/spin-framework/src/
├── Application.php           Main application class orchestrating requests
├── Core/                     Core framework components
│   ├── Cache/
│   ├── Cache/Adapters/       Cache implementations (Redis, APCu, File)
│   ├── ConnectionManager.php  Database connection pooling
│   ├── Controller.php         Base controller class
│   ├── Middleware.php         Base middleware class
│   ├── Route.php              Route representation
│   ├── RouteGroup.php         Route group handler
│   ├── Logger.php             Application logger
│   └── ...
├── Database/                 Database layer
│   └── Drivers/Pdo/           PDO-based drivers (MySQL, PostgreSQL, SQLite, etc.)
├── Helpers/                  Global helper functions
│   ├── Cipher.php
│   ├── JWT.php
│   ├── UUID.php
│   └── ...
├── Factories/                PSR-17 HTTP message factories
├── Classes/                  Special purpose classes
├── Exceptions/               Framework exception classes
└── (other files)
```

You don't normally modify framework code. Think of it as your foundation.

## Your Application Structure

When you create a SPIN application, organize your code in a `src/app/` directory (following PSR-4 namespace `App\`).

### Recommended Structure

```
my-app/
├── src/
│   └── app/
│       ├── Config/
│       │   ├── version.json        Application identity (code, name, version)
│       │   ├── config.json         Environment-independent config
│       │   ├── config-dev.json     Development config
│       │   ├── config-prod.json    Production config
│       │   ├── routes.json         Environment-independent routes
│       │   ├── routes-dev.json     Development routes
│       │   └── routes-prod.json    Production routes
│       ├── Controllers/
│       │   ├── Api/
│       │   │   ├── v1/
│       │   │   │   └── tasks/      Grouping by resource/version
│       │   │   │       └── TaskController.php
│       │   │   └── HealthController.php
│       │   └── IndexController.php
│       ├── Middlewares/
│       │   ├── AuthBeforeMiddleware.php
│       │   ├── LoggingAfterMiddleware.php
│       │   └── ...
│       ├── Models/
│       │   └── Task.php            Data models (optional)
│       ├── Services/               Business logic (optional)
│       │   └── TaskService.php
│       ├── Classes/
│       │   └── Managers/           Utility managers
│       │       └── SessionManager.php
│       └── Globals.php             Register global helpers
├── public/
│   ├── index.php                   Entry point
│   ├── .htaccess                   Apache URL rewriting (optional)
│   └── (assets, images, etc.)
├── storage/                        Generated files, caches
│   ├── logs/
│   ├── cache/
│   └── uploads/
├── tests/                          Unit and integration tests
├── vendor/                         Composer dependencies
├── composer.json
├── composer.lock
└── .env                           Environment variables (optional, auto-loaded at startup)
```

## Configuration Files

Configuration is environment-based and uses JSON:

### `version.json` (required)

Sets your application's identity. The framework loads this automatically at startup before any other config file:

```json
{
    "application": {
        "code": "my-app",
        "name": "My Application",
        "version": "1.0.0"
    }
}
```

| Field | Purpose |
|-------|---------|
| `code` | Machine identifier — used as Monolog log channel name and shared-storage path suffix |
| `name` | Human-readable application label |
| `version` | Semver version string |

Access at runtime:

```php
app()->getAppCode();    // "my-app"
app()->getAppName();    // "My Application"
app()->getAppVersion(); // "1.0.0"
```

### `config.json` (shared)
Global settings used across all environments:

```json
{
  "application": {
    "global": {
      "maintenance": false,
      "message": "We are in maintenance mode, back shortly",
      "timezone": "UTC"
    },
    "secret": "${env:APPLICATION_SECRET}"
  }
}
```

### `config-dev.json` (environment-specific)
Development overrides and specific config:

```json
{
  "application": {
    "global": {
      "maintenance": false,
      "message": "We are in maintenance mode, back shortly",
      "timezone": "UTC"
    },
    "secret": "${env:APPLICATION_SECRET}"
  },
  "logger": {
    "level": "debug",
    "driver": "file",
    "drivers": {
      "file": {
        "file_path": "storage/log",
        "file_format": "Y-m-d",
        "line_format": "[%datetime%] [%channel%] [%level_name%] %message% %context%\n",
        "line_datetime": "Y-m-d H:i:s.v e"
      }
    }
  },
  "connections": {
    "mysql": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "${env:DB_DATABASE}",
      "host": "${env:DB_HOST}",
      "port": "${env:DB_PORT}",
      "username": "${env:DB_USERNAME}",
      "password": "${env:DB_PASSWORD}",
      "charset": "UTF8",
      "options": {
        "ATTR_PERSISTENT": false,
        "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT": false
      }
    }
  }
}
```

> **Logger `line_format` and line endings:** On Linux, Docker, and Unix systems the file driver may not append a newline after each entry. Add `\n` at the end of `line_format` to ensure each log entry ends with a newline. On Windows this is not required but harmless.

### `config-prod.json`
Production settings with hardened defaults.

### Environment Variables

Reference variables from `.env` or system environment:

```json
"password": "${env:DATABASE_PASSWORD}"
```

Access at runtime:
```php
$dbPassword = env('DATABASE_PASSWORD');
```

## Routes Files

Routes are defined in JSON with hierarchical structure:

### `routes.json` (shared)
Common middleware and error handlers used everywhere.

### `routes-dev.json` (environment-specific)
Development-specific routes and middleware.

```json
{
  "common": {
    "before": [],
    "after": ["\\App\\Middlewares\\LoggingAfterMiddleware"]
  },
  "groups": [
    {
      "name": "API",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\AuthBeforeMiddleware"],
      "routes": [
        {
          "methods": ["GET"],
          "path": "/tasks",
          "handler": "\\App\\Controllers\\Api\\v1\\tasks\\TaskController"
        }
      ],
      "after": []
    }
  ],
  "errors": {
    "4xx": "\\App\\Controllers\\Error4xxController",
    "5xx": "\\App\\Controllers\\Error5xxController"
  }
}
```

## Where to Put Your Code

### Controllers
**Location:** `src/app/Controllers/`
**Namespace:** `App\Controllers\`
**Purpose:** Handle HTTP requests, return responses

```php
namespace App\Controllers\Api\v1\tasks;

class TaskController extends Controller
{
    public function handleGET(array $args): ResponseInterface { ... }
}
```

### Middleware
**Location:** `src/app/Middlewares/`
**Namespace:** `App\Middlewares\`
**Purpose:** Process requests before/after controllers

```php
namespace App\Middlewares;

class AuthBeforeMiddleware extends Middleware
{
    public function initialize(array $args): bool { ... }
    public function handle(array $args): bool { ... }
}
```

### Models & Services
**Location:** `src/app/Models/` and `src/app/Services/`
**Namespace:** `App\Models\` and `App\Services\`
**Purpose:** Data models and business logic

```php
namespace App\Models;

class Task
{
    public int $id;
    public string $title;
    // ...
}
```

### Utilities
**Location:** `src/app/Classes/`
**Namespace:** `App\Classes\`
**Purpose:** Helper classes, managers, utilities

## Key Files

### `public/index.php`
Entry point for all requests. Bootstraps the application:

```php
$app = new Application(
    projectPath: dirname(__DIR__),
    configFile: 'routes.json',
    environment: $_ENV['APP_ENV'] ?? 'dev'
);
$app->run();
```

### `.env` (optional)
Store environment-specific secrets and settings. SPIN automatically loads this file at
startup — variables become available to `${env:VAR}` macros in config files and to the
`env()` helper function. Real environment variables (OS, Docker, CI) always take
precedence over `.env` values.

```
APP_ENV=dev
DB_HOST=localhost
DB_USER=myuser
DB_PASS=mypassword
```

Never commit `.env` to version control. Add `.env` to `.gitignore`.

## Naming Conventions

| Item | Pattern | Example |
|------|---------|---------|
| Controllers | `{Name}Controller` | `TaskController`, `HealthController` |
| Middleware | `{Name}Middleware` | `AuthBeforeMiddleware`, `LoggingAfterMiddleware` |
| Config files | `config{-env}.json` | `config.json`, `config-dev.json` |
| Route files | `routes{-env}.json` | `routes.json`, `routes-dev.json` |
| Classes | `{Name}` (PascalCase) | `TaskService`, `SessionManager` |
| Methods | `handle{METHOD}` | `handleGET`, `handlePOST` |
| Namespaces | `App\{Area}` | `App\Controllers`, `App\Middlewares` |

## PSR-4 Autoloading

Configure in `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/app/",
      "Spin\\": "vendor/celarius/spin-framework/src/"
    }
  }
}
```

Run `composer dump-autoload` after adding classes.

## Storage Structure

Create these directories for generated files:

```
storage/
├── logs/          Application logs
├── cache/         Cache files
├── uploads/       User-uploaded files
└── sessions/      Session data (if applicable)
```

Add to `.gitignore`:
```
storage/
public/uploads/
.env
.env.local
```

## See Also

- [Core-Concepts.md](Core-Concepts.md) — How routing and middleware work
- [Your-First-App.md](Your-First-App.md) — Building a complete application
- [User-Guide/Configuration.md](../User-Guide/Configuration.md) — Configuration in depth
- [User-Guide/Routing.md](../User-Guide/Routing.md) — Advanced routing patterns
