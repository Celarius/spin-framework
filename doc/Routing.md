# Routing

SPIN Framework uses a JSON-based routing system that defines routes, middleware, and controllers in a structured configuration file. Routes are organized into groups with shared middleware and prefixes.

## Route Configuration

SPIN applications define routes in JSON configuration files (e.g., `routes-dev.json`) that specify the routing structure, middleware, and controller mappings.

### Basic Route Configuration Structure

```json
{
  "common": {
    "before": [],
    "after": [
      "\\App\\Middlewares\\RequestIdAfterMiddleware",
      "\\App\\Middlewares\\ResponseTimeAfterMiddleware",
      "\\App\\Middlewares\\ResponseLogAfterMiddleware"
    ]
  },
  "groups": [
    {
      "name": "unversioned api endpoints",
      "notes": "",
      "prefix": "/api",
      "before": [],
      "routes": [
        { "methods":["GET"], "path":"/health", "handler":"\\App\\Controllers\\Api\\HealthController" },
        { "methods":["GET"], "path":"/status", "handler":"\\App\\Controllers\\Api\\StatusController" },
        { "methods":["GET"], "path":"/info", "handler":"\\App\\Controllers\\Api\\InfoController" }
      ],
      "after": []
    },
    {
      "name":"Public",
      "notes": "Public endpoints",
      "prefix": "/api/v1",
      "before": [],
      "routes": [],
      "after": []
    },
    {
      "name":"Private",
      "notes": "Private endpoints",
      "prefix": "/api/v1",
      "before": [
        "\\App\\Middlewares\\AuthHttpBeforeMiddleware"
      ],
      "routes": [],
      "after": []
    },
    {
      "name":"Default",
      "notes": "Default route if nothing else matches",
      "prefix": "",
      "before": [
        "\\App\\Middlewares\\SessionBeforeMiddleware"
      ],
      "routes": [
        { "methods":[], "path":"/", "handler":"\\App\\Controllers\\IndexController" }
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

## Route Groups

Route groups allow you to organize related routes with shared middleware and prefixes.

### Group Structure

```json
{
  "name": "Group Name",
  "notes": "Description of the group",
  "prefix": "/api/v1",
  "before": ["\\App\\Middlewares\\AuthMiddleware"],
  "routes": [
    // Route definitions
  ],
  "after": ["\\App\\Middlewares\\LoggingMiddleware"]
}
```

### Group Properties

- **name**: Descriptive name for the route group
- **notes**: Additional information about the group
- **prefix**: URL prefix applied to all routes in the group
- **before**: Middleware executed before route handling
- **routes**: Array of route definitions
- **after**: Middleware executed after route handling

## Route Definitions

Individual routes define the HTTP methods, path, and controller handler.

### Route Structure

```json
{
  "methods": ["GET", "POST"],
  "path": "/users/{id}",
  "handler": "\\App\\Controllers\\UserController"
}
```

### Route Properties

- **methods**: Array of HTTP methods (GET, POST, PUT, DELETE, etc.)
- **path**: URL path with optional parameters
- **handler**: Fully qualified class name of the controller

### HTTP Methods

```json
// Single method
{ "methods": ["GET"], "path": "/users", "handler": "\\App\\Controllers\\UserController" }

// Multiple methods
{ "methods": ["GET", "POST"], "path": "/users", "handler": "\\App\\Controllers\\UserController" }

// All methods (empty array)
{ "methods": [], "path": "/", "handler": "\\App\\Controllers\\IndexController" }
```

## Route Parameters

SPIN supports route parameters using curly brace syntax.

### Parameter Examples

```json
// Single parameter
{ "methods": ["GET"], "path": "/users/{id}", "handler": "\\App\\Controllers\\UserController" }

// Multiple parameters
{ "methods": ["GET"], "path": "/users/{id}/posts/{postId}", "handler": "\\App\\Controllers\\PostController" }

// Optional parameters (using ?)
{ "methods": ["GET"], "path": "/users/{id?}", "handler": "\\App\\Controllers\\UserController" }
```

## Controllers

Controllers handle the business logic for routes and extend SPIN's base controller classes.

### Controller Structure

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use \App\Controllers\AbstractPlatesController;

class IndexController extends AbstractPlatesController
{
  /**
   * Handle GET request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   */
  public function handleGET(array $args)
  {
    # Model to send to view
    $model = ['title'=>'PageTitle', 'user'=>'Friend'];

    # Render view
    $html = $this->engine->render('pages::index', $model);

    # Send the generated html
    return response($html);
  }
}
```

### HTTP Method Handlers

SPIN controllers use specific method names to handle different HTTP requests:

- `handleGET(array $args)` - Handles GET requests
- `handlePOST(array $args)` - Handles POST requests
- `handlePUT(array $args)` - Handles PUT requests
- `handleDELETE(array $args)` - Handles DELETE requests
- `handlePATCH(array $args)` - Handles PATCH requests

### Controller Parameters

The `$args` parameter contains route parameters as key-value pairs:

```php
// Route: /users/{id}/posts/{postId}
// URL: /users/123/posts/456

public function handleGET(array $args)
{
    $userId = $args['id'];        // "123"
    $postId = $args['postId'];    // "456"
    
    // Controller logic here
}
```

## Abstract Controllers

SPIN provides abstract controller classes for common functionality.

### AbstractPlatesController

For template-based responses using the Plates template engine:

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use \App\Controllers\AbstractPlatesController;

class UserController extends AbstractPlatesController
{
    public function handleGET(array $args)
    {
        $user = getUserById($args['id']);
        $html = $this->engine->render('users::show', ['user' => $user]);
        return response($html);
    }
}
```

### AbstractRestController

For API responses returning JSON:

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use \App\Controllers\AbstractRestController;

class ApiController extends AbstractRestController
{
    public function handleGET(array $args)
    {
        $data = ['status' => 'success', 'message' => 'Hello World'];
        return responseJson($data);
    }
}
```

## Middleware Integration

Routes can have middleware applied at multiple levels:

### Common Middleware

Applied to all routes in the application:

```json
{
  "common": {
    "before": ["\\App\\Middlewares\\RequestIdBeforeMiddleware"],
    "after": ["\\App\\Middlewares\\ResponseLogAfterMiddleware"]
  }
}
```

### Group Middleware

Applied to all routes in a specific group:

```json
{
  "name": "Private",
  "prefix": "/api/v1",
  "before": ["\\App\\Middlewares\\AuthHttpBeforeMiddleware"],
  "routes": [
    { "methods": ["GET"], "path": "/profile", "handler": "\\App\\Controllers\\ProfileController" }
  ]
}
```

### Route-Specific Middleware

Individual routes can specify additional middleware:

```json
{
  "methods": ["POST"],
  "path": "/users",
  "handler": "\\App\\Controllers\\UserController",
  "middleware": ["\\App\\Middlewares\\ValidationMiddleware"]
}
```

## Error Handling

SPIN provides error controllers for handling HTTP error responses.

### Error Controllers

```json
{
  "errors": {
    "4xx": "\\App\\Controllers\\Error4xxController",
    "5xx": "\\App\\Controllers\\Error5xxController"
  }
}
```

### Error Controller Example

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use \App\Controllers\AbstractPlatesController;

class Error4xxController extends AbstractPlatesController
{
    public function handleGET(array $args)
    {
        $statusCode = $args['status'] ?? 404;
        $html = $this->engine->render('errors::4xx', ['status' => $statusCode]);
        return response($html, $statusCode);
    }
}
```

## Response Handling

SPIN provides helper functions for creating responses.

### Response Helpers

```php
// HTML response
return response($html);

// JSON response
return responseJson($data);

// Response with status code
return response($html, 201);

// Response with headers
return response($html, 200, ['Content-Type' => 'text/html']);

// JSON response with status
return responseJson($data, 201);
```

## Route Caching

SPIN caches route definitions for performance. Changes to route configuration require an application restart.

## Best Practices

1. **Route Organization**: Group related routes together with descriptive names
2. **Middleware Order**: Apply authentication middleware early in the pipeline
3. **Error Handling**: Provide meaningful error responses for different HTTP status codes
4. **Controller Methods**: Use specific HTTP method handlers for better organization
5. **Route Parameters**: Use descriptive parameter names and validate them in controllers
6. **Response Consistency**: Use consistent response formats across your API
7. **Documentation**: Document route groups and their purposes

## Example Route Configuration

Here's a complete example of a typical SPIN application route configuration:

```json
{
  "common": {
    "before": ["\\App\\Middlewares\\RequestIdBeforeMiddleware"],
    "after": [
      "\\App\\Middlewares\\ResponseTimeAfterMiddleware",
      "\\App\\Middlewares\\ResponseLogAfterMiddleware"
    ]
  },
  "groups": [
    {
      "name": "Public API",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\CorsBeforeMiddleware"],
      "routes": [
        { "methods": ["GET"], "path": "/health", "handler": "\\App\\Controllers\\Api\\HealthController" },
        { "methods": ["POST"], "path": "/auth/login", "handler": "\\App\\Controllers\\Api\\AuthController" }
      ]
    },
    {
      "name": "Protected API",
      "prefix": "/api/v1",
      "before": [
        "\\App\\Middlewares\\AuthHttpBeforeMiddleware",
        "\\App\\Middlewares\\RateLimitBeforeMiddleware"
      ],
      "routes": [
        { "methods": ["GET"], "path": "/users/{id}", "handler": "\\App\\Controllers\\Api\\UserController" },
        { "methods": ["POST"], "path": "/users", "handler": "\\App\\Controllers\\Api\\UserController" },
        { "methods": ["PUT"], "path": "/users/{id}", "handler": "\\App\\Controllers\\Api\\UserController" },
        { "methods": ["DELETE"], "path": "/users/{id}", "handler": "\\App\\Controllers\\Api\\UserController" }
      ]
    },
    {
      "name": "Web Pages",
      "prefix": "",
      "before": ["\\App\\Middlewares\\SessionBeforeMiddleware"],
      "routes": [
        { "methods": [], "path": "/", "handler": "\\App\\Controllers\\IndexController" },
        { "methods": ["GET"], "path": "/about", "handler": "\\App\\Controllers\\PageController" },
        { "methods": ["GET"], "path": "/contact", "handler": "\\App\\Controllers\\PageController" }
      ]
    }
  ],
  "errors": {
    "4xx": "\\App\\Controllers\\Error4xxController",
    "5xx": "\\App\\Controllers\\Error5xxController"
  }
}
```

This routing system provides a clean, organized way to define your application's URL structure while maintaining flexibility for middleware and controller organization.
