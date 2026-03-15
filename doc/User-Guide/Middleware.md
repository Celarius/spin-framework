# Middleware

SPIN Framework uses a middleware system that allows you to intercept and modify HTTP requests and responses. Middleware extends the `Spin\Core\Middleware` class and provides a clean way to handle cross-cutting concerns like authentication, logging, and CORS.

## Middleware Structure

SPIN middleware classes extend `Spin\Core\Middleware` and implement two main methods:

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class ExampleMiddleware extends Middleware
{
    /**
     * Initialize the middleware
     *
     * @param array $args The arguments
     * @return bool
     */
    public function initialize(array $args): bool
    {
        // Setup code here
        return true;
    }

    /**
     * Handle the request/response
     *
     * @param array $args URI parameters as key=value array
     * @return bool True=OK, False=Failed to handle it
     */
    public function handle(array $args): bool
    {
        // Middleware logic here
        return true;
    }
}
```

## Middleware Methods

### initialize(array $args): bool

Called once when the middleware is created. Use this method for:
- Loading configuration
- Setting up dependencies
- Initializing resources

Return `true` if initialization succeeds, `false` if it fails.

### handle(array $args): bool

Called for each request. Use this method for:
- Processing the request
- Modifying the response
- Performing validation

Return `true` if the request should continue, `false` if it should be blocked.

## Helper Functions

SPIN provides several helper functions that middleware can use:

### Configuration Access

```php
// Get configuration values
$secret = config('application.secret');
$timeout = config('session.timeout');
$logLevel = config('logger.level', 'info');
```

### Container Access

```php
// Store values in the container
container('user', $user);
container('requestId', $requestId);

// Retrieve values from the container
$user = container('user');
$requestId = container('requestId');
```

### Request/Response Access

```php
// Get the current request
$request = getRequest();

// Get the current response
$response = getResponse();

// Create a new response
return response($content, $statusCode, $headers);
```

### Logging

```php
// Log messages
logger()->info('User authenticated', ['userId' => $userId]);
logger()->error('Authentication failed', ['error' => $error]);
logger()->critical('Critical error', ['trace' => $trace]);
```

## Middleware Examples

### Authentication Middleware

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;
use Spin\Helpers\JWT;

class AuthHttpBeforeMiddleware extends Middleware
{
    /** @var string Secret string */
    protected $secret;

    /**
     * Initialize
     */
    public function initialize(array $args): bool
    {
        # Get applications global secret
        $this->secret = config('application.secret');
        return true;
    }

    /**
     * Handle authentication
     */
    public function handle(array $args): bool
    {
        $authenticated = false;
        $type = 'token';
        $token = config('integrations.core.token');
        $authenticated = $this->authToken($token);

        # Failed authentication
        if (!$authenticated && getResponse()->getStatusCode() < 400) {
            response('', 401, [
                'WWW-Authenticate' => $type . ' realm="' . 
                    (getRequest()->getHeader('Host')[0] ?? '') . '"'
            ]);
        }

        return $authenticated;
    }

    /**
     * Token authentication
     */
    protected function authToken(string $token): bool
    {
        $authenticated = false;
        $tokens = config('tokens') ?? [];
        $authenticated = array_key_exists($token, $tokens);
        return $authenticated;
    }

    /**
     * Bearer authentication (JWT)
     */
    protected function authBearer(string $token): bool
    {
        $authenticated = false;

        try {
            # Verify the Token and decode the payload
            $payload = JWT::decode($token, $this->secret, ['HS256']);

            if (!is_null($payload)) {
                # Store the Payload in the Dependency Container
                container('jwt:payload', $payload);
                $authenticated = true;
            }
        } catch (\Exception $e) {
            logger()->critical($e->getMessage(), [
                'rid' => container('requestId'),
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $authenticated;
    }
}
```

### Session Middleware

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class SessionBeforeMiddleware extends Middleware
{
    /**
     * Retrieve or Set the SessionId cookie for the request
     */
    public function handle(array $args): bool
    {
        # Build session array
        $session = [];
        $session['cookie'] = config('session.cookie');
        $session['timeout'] = config('session.timeout');
        $session['refresh'] = config('session.refresh');
        
        if (!empty($session['cookie'])) {
            $session['value'] = cookie($session['cookie']);
        } else {
            $session['value'] = '';
        }

        # Set array to container
        container('session', $session);

        return true;
    }
}
```

### CORS Middleware

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class CorsBeforeMiddleware extends Middleware
{
    /**
     * Handle CORS preflight and add CORS headers
     */
    public function handle(array $args): bool
    {
        $request = getRequest();
        $response = getResponse();

        # Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            $response = $response->withStatus(200);
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
            $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            return true;
        }

        # Add CORS headers to all responses
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        return true;
    }
}
```

### Request ID Middleware

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class RequestIdBeforeMiddleware extends Middleware
{
    /**
     * Generate and set request ID
     */
    public function handle(array $args): bool
    {
        $requestId = uniqid('req_', true);
        container('requestId', $requestId);
        
        # Add request ID to response headers
        $response = getResponse();
        $response = $response->withHeader('X-Request-ID', $requestId);
        
        return true;
    }
}
```

### Response Logging Middleware

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class ResponseLogAfterMiddleware extends Middleware
{
    /**
     * Log response information
     */
    public function handle(array $args): bool
    {
        $response = getResponse();
        $request = getRequest();
        $requestId = container('requestId');
        
        logger()->info('Response completed', [
            'rid' => $requestId,
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'status' => $response->getStatusCode(),
            'size' => $response->getBody()->getSize()
        ]);
        
        return true;
    }
}
```

## Middleware Registration

Middleware is registered in the route configuration file (`routes-dev.json`):

### Common Middleware

Applied to all routes:

```json
{
  "common": {
    "before": [
      "\\App\\Middlewares\\RequestIdBeforeMiddleware"
    ],
    "after": [
      "\\App\\Middlewares\\ResponseTimeAfterMiddleware",
      "\\App\\Middlewares\\ResponseLogAfterMiddleware"
    ]
  }
}
```

### Group Middleware

Applied to specific route groups:

```json
{
  "name": "Private",
  "prefix": "/api/v1",
  "before": [
    "\\App\\Middlewares\\AuthHttpBeforeMiddleware"
  ],
  "routes": [
    { "methods": ["GET"], "path": "/profile", "handler": "\\App\\Controllers\\ProfileController" }
  ]
}
```

### Route-Specific Middleware

Applied to individual routes:

```json
{
  "methods": ["POST"],
  "path": "/users",
  "handler": "\\App\\Controllers\\UserController",
  "middleware": [
    "\\App\\Middlewares\\ValidationMiddleware"
  ]
}
```

## Middleware Order

Middleware execution order is important:

1. **Common Before** - Applied to all routes
2. **Group Before** - Applied to route groups
3. **Route Before** - Applied to specific routes
4. **Controller** - Route handler execution
5. **Route After** - Applied to specific routes
6. **Group After** - Applied to route groups
7. **Common After** - Applied to all routes

## Middleware Best Practices

1. **Single Responsibility**: Each middleware should handle one concern
2. **Performance**: Keep middleware lightweight and efficient
3. **Error Handling**: Always handle exceptions gracefully
4. **Logging**: Log important events and errors
5. **Configuration**: Use configuration for customizable behavior
6. **Testing**: Write unit tests for middleware logic
7. **Documentation**: Document middleware purpose and behavior

## Conditional Middleware

Middleware can be conditionally applied based on request properties:

```php
public function handle(array $args): bool
{
    $request = getRequest();
    
    # Only apply to API routes
    if (!str_starts_with($request->getUri()->getPath(), '/api/')) {
        return true;
    }
    
    # Apply middleware logic
    return $this->processApiRequest($args);
}
```

## Middleware Parameters

Middleware can receive parameters from the route configuration:

```json
{
  "before": [
    "\\App\\Middlewares\\RateLimitMiddleware:100,60"
  ]
}
```

```php
public function initialize(array $args): bool
{
    # Parse parameters (e.g., "100,60" -> max 100 requests per 60 seconds)
    $params = explode(',', $args['params'] ?? '100,60');
    $this->maxRequests = (int)($params[0] ?? 100);
    $this->timeWindow = (int)($params[1] ?? 60);
    
    return true;
}
```

## Error Handling in Middleware

Always handle errors gracefully in middleware:

```php
public function handle(array $args): bool
{
    try {
        // Middleware logic
        return $this->processRequest($args);
    } catch (\Exception $e) {
        logger()->error('Middleware error: ' . $e->getMessage(), [
            'rid' => container('requestId'),
            'middleware' => static::class,
            'error' => $e->getMessage()
        ]);
        
        // Return false to block the request or true to continue
        return false;
    }
}
```

## Testing Middleware

Test middleware in isolation:

```php
<?php
// tests/Middleware/AuthMiddlewareTest.php

use PHPUnit\Framework\TestCase;

class AuthMiddlewareTest extends TestCase
{
    public function testSuccessfulAuthentication()
    {
        $middleware = new AuthHttpBeforeMiddleware();
        $args = ['token' => 'valid_token'];
        
        $result = $middleware->handle($args);
        
        $this->assertTrue($result);
    }
    
    public function testFailedAuthentication()
    {
        $middleware = new AuthHttpBeforeMiddleware();
        $args = ['token' => 'invalid_token'];
        
        $result = $middleware->handle($args);
        
        $this->assertFalse($result);
    }
}
```

This middleware system provides a powerful and flexible way to handle cross-cutting concerns in your SPIN application while maintaining clean separation of concerns.
