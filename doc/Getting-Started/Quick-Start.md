# Quick Start — Get SPIN Running in 5 Minutes

Get your first SPIN Framework application up and running in just a few minutes. This guide walks you through installation, creating a route, and testing your first endpoint.

## Prerequisites

- PHP 8.0 or higher
- Composer
- Basic familiarity with command line
- A text editor or IDE

## Installation

Create a new project directory:

```bash
mkdir my-first-app
cd my-first-app
```

Initialize a Composer project:

```bash
composer init
```

Answer the prompts (or use defaults), then add SPIN Framework as a dependency:

```bash
composer require celarius/spin-framework
```

Create the project structure:

```bash
mkdir -p src/app/Controllers
mkdir -p src/app/Config
mkdir public
```

## Set Your Application Identity

Create `src/app/Config/version.json`:

```json
{
    "application": {
        "code": "my-first-app",
        "name": "My First App",
        "version": "0.1.0"
    }
}
```

The framework loads this file automatically at startup. Access the values at runtime:

```php
app()->getAppCode();    // "my-first-app"
app()->getAppName();    // "My First App"
app()->getAppVersion(); // "0.1.0"
```

> `code` is also used as the Monolog log channel name and storage path identifier.

## Create Your First Controller

Create `src/app/Controllers/WelcomeController.php`:

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class WelcomeController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        return responseJson([
            'message' => 'Welcome to SPIN Framework!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
```

## Define Your Routes

Create `src/app/Config/routes.json`:

```json
{
  "common": {
    "before": [],
    "after": []
  },
  "groups": [
    {
      "name": "Welcome Routes",
      "notes": "Basic welcome endpoint",
      "prefix": "/api",
      "before": [],
      "routes": [
        {
          "methods": ["GET"],
          "path": "/welcome",
          "handler": "\\App\\Controllers\\WelcomeController"
        }
      ],
      "after": []
    }
  ],
  "errors": {}
}
```

## Bootstrap Your Application

Create `public/index.php`:

```php
<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Spin\Application;

$app = new Application(
    projectPath: dirname(__DIR__),
    configFile: 'routes.json',
    environment: 'dev'
);

$app->run();
```

## Start the Development Server

Use PHP's built-in server:

```bash
php -S localhost:8000 -t public/
```

You'll see output like:
```
Development Server (http://localhost:8000)
Listening on http://localhost:8000
```

## Test Your Endpoint

In a new terminal, test your endpoint:

```bash
curl http://localhost:8000/api/welcome
```

Or using Postman:
1. Create a new GET request to `http://localhost:8000/api/welcome`
2. Send the request
3. See the JSON response

You should receive:
```json
{
  "message": "Welcome to SPIN Framework!",
  "timestamp": "2026-03-15 14:23:45"
}
```

## What You've Built

- **Controller** — PHP class handling the GET request
- **Route** — JSON definition mapping `/api/welcome` to your controller
- **Global Helper** — `responseJson()` sends JSON responses
- **Application** — Main orchestrator loading config and routing requests

## Next Steps

- Read [Project-Structure.md](Project-Structure.md) to understand the framework layout
- Explore [Core-Concepts.md](Core-Concepts.md) to understand how it all works
- Try [Your-First-App.md](Your-First-App.md) to build a complete CRUD API
- Check [User-Guide/Routing.md](../User-Guide/Routing.md) for advanced routing patterns

## Troubleshooting

**Port 8000 already in use?**
```bash
php -S localhost:8001 -t public/
```

**Routes not found (404)?**
- Verify route path matches your request exactly (case-sensitive)
- Check controller fully-qualified class name matches route handler
- Restart the development server after config changes

**Blank response?**
- Check PHP error logs: `php -S localhost:8000 -t public/ 2>&1`
- Verify namespace and controller class name are correct

## See Also

- [spin-skeleton](https://github.com/Celarius/spin-skeleton) — Complete example application
- [User-Guide/Configuration.md](../User-Guide/Configuration.md) — Configuration details
- [User-Guide/Routing.md](../User-Guide/Routing.md) — Advanced routing features
