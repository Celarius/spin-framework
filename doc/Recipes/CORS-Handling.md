# CORS Handling Recipe

## Problem

How do I handle cross-origin requests (CORS) in my SPIN Framework API?

This guide covers CORS headers, preflight requests, middleware implementation, and common pitfalls.

---

## Solution

Implement CORS by setting appropriate HTTP headers and handling preflight OPTIONS requests. SPIN provides middleware for automatic CORS handling.

---

## Understanding CORS

CORS (Cross-Origin Resource Sharing) prevents malicious websites from accessing your API without permission. When a browser makes a cross-origin request, it first sends a preflight OPTIONS request to verify the server allows it.

**Key Concepts:**
- **Origin:** Protocol + Domain + Port (e.g., https://example.com:8080)
- **Preflight:** OPTIONS request sent before actual request
- **Credentials:** Cookies and auth headers are blocked by default

---

## Basic CORS Middleware

```php
<?php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class CorsMiddleware extends Middleware
{
    private array $allowedOrigins = [];
    private bool $allowCredentials = false;
    private array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
    private array $allowedHeaders = ['Content-Type', 'Authorization'];
    private array $exposedHeaders = [];
    private int $maxAge = 86400; // 24 hours

    public function initialize(array $args): bool
    {
        // Load configuration
        $this->allowedOrigins = $args['allowed_origins'] ?? [];
        $this->allowCredentials = $args['allow_credentials'] ?? false;
        $this->allowedMethods = $args['allowed_methods'] ?? $this->allowedMethods;
        $this->allowedHeaders = $args['allowed_headers'] ?? $this->allowedHeaders;
        $this->exposedHeaders = $args['exposed_headers'] ?? $this->exposedHeaders;
        $this->maxAge = $args['max_age'] ?? $this->maxAge;

        return true;
    }

    public function handle(array $args): bool
    {
        $origin = getRequest()->getHeaderLine('Origin') ?? '';

        // Check if origin is allowed
        if (!$this->isOriginAllowed($origin)) {
            // Don't set CORS headers if origin not allowed
            if ($this->getRequest()->getMethod() === 'OPTIONS') {
                response(responseJson(['error' => 'CORS not allowed'], 403));
                return false;
            }
            return true;
        }

        // Add CORS headers
        $response = getResponse()
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders))
            ->withHeader('Access-Control-Max-Age', (string)$this->maxAge);

        if (!empty($this->exposedHeaders)) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $this->exposedHeaders)
            );
        }

        if ($this->allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        response($response);

        // Handle preflight requests
        if (getRequest()->getMethod() === 'OPTIONS') {
            return responseJson([], 204);
        }

        return true;
    }

    private function isOriginAllowed(string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }

        // Allow all origins
        if (in_array('*', $this->allowedOrigins, true)) {
            return true;
        }

        // Exact match
        if (in_array($origin, $this->allowedOrigins, true)) {
            return true;
        }

        // Wildcard matching
        foreach ($this->allowedOrigins as $pattern) {
            if ($this->matchesPattern($origin, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function matchesPattern(string $origin, string $pattern): bool
    {
        // Convert pattern like https://*.example.com to regex
        $pattern = str_replace('.', '\.', $pattern);
        $pattern = str_replace('*', '.*', $pattern);

        return (bool)preg_match("#^$pattern$#", $origin);
    }
}
```

---

## Configuration via Environment

```php
<?php
// In your configuration file or bootstrap

return [
    'cors' => [
        'enabled' => env('CORS_ENABLED', true),
        'allowed_origins' => explode(',', env(
            'CORS_ALLOWED_ORIGINS',
            'http://localhost:3000,http://localhost:8080'
        )),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => ['X-Total-Count', 'X-Page-Number', 'X-RateLimit-Remaining'],
        'allow_credentials' => env('CORS_ALLOW_CREDENTIALS', false),
        'max_age' => 86400,
    ],
];
```

Environment variables:

```env
CORS_ENABLED=true
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8080,https://app.example.com
CORS_ALLOW_CREDENTIALS=false
```

---

## Route-Specific CORS

Apply different CORS policies to different endpoint groups:

```json
{
  "groups": [
    {
      "prefix": "/api/public",
      "middleware": [
        {
          "name": "cors",
          "args": {
            "allowed_origins": ["*"],
            "allowed_methods": ["GET", "OPTIONS"],
            "allow_credentials": false
          }
        }
      ],
      "routes": [
        {
          "path": "/posts",
          "method": "GET",
          "controller": "PostController",
          "handler": "handleGET"
        }
      ]
    },
    {
      "prefix": "/api/v1",
      "middleware": [
        {
          "name": "cors",
          "args": {
            "allowed_origins": [
              "https://app.example.com",
              "https://*.example.com"
            ],
            "allowed_methods": ["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS"],
            "allow_credentials": true,
            "exposed_headers": ["X-Total-Count", "X-RateLimit-Remaining"]
          }
        }
      ],
      "routes": [
        {
          "path": "/users",
          "method": "GET",
          "controller": "UserController",
          "handler": "handleGET"
        }
      ]
    }
  ]
}
```

---

## Handling Credentials with CORS

When allowing credentials (cookies, Authorization headers), be more restrictive:

```php
<?php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class CredentialsCorsMiddleware extends Middleware
{
    public function handle(array $args): bool
    {
        $origin = getRequest()->getHeaderLine('Origin') ?? '';

        // Only allow specific origins with credentials
        $allowedOrigins = [
            'https://app.example.com',
            'https://admin.example.com',
        ];

        if (!in_array($origin, $allowedOrigins, true)) {
            if (getRequest()->getMethod() === 'OPTIONS') {
                response(responseJson(['error' => 'CORS not allowed'], 403));
                return false;
            }
            return true;
        }

        $response = getResponse()
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Max-Age', '86400');

        response($response);

        if (getRequest()->getMethod() === 'OPTIONS') {
            return responseJson([], 204);
        }

        return true;
    }
}
```

---

## JavaScript Client Usage

```javascript
// GET with CORS
fetch('https://api.example.com/posts', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
    },
    // No credentials by default
})
.then(r => r.json())
.then(data => console.log(data));

// POST with CORS
fetch('https://api.example.com/posts', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({ title: 'New Post' }),
})
.then(r => r.json())
.then(data => console.log(data));

// With credentials (cookies, auth headers)
fetch('https://api.example.com/user/profile', {
    method: 'GET',
    credentials: 'include', // Send cookies and auth
    headers: {
        'Authorization': `Bearer ${token}`,
    },
})
.then(r => r.json())
.then(data => console.log(data));

// Handle CORS errors
fetch('https://api.example.com/data')
    .catch(error => {
        // TypeError: Failed to fetch (CORS issue)
        console.error('CORS Error:', error);
    });
```

---

## Common CORS Issues and Solutions

### Issue 1: Preflight Request Fails

```
OPTIONS https://api.example.com/data 403

Error: Response to preflight request doesn't pass access control check
```

**Solution:** Ensure middleware handles OPTIONS requests:

```php
public function handle(array $args): bool
{
    // Add CORS headers BEFORE checking if it's OPTIONS
    $response = getResponse()
        ->withHeader('Access-Control-Allow-Origin', $origin)
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type');

    response($response);

    // Handle OPTIONS requests
    if (getRequest()->getMethod() === 'OPTIONS') {
        return responseJson([], 204);
    }

    return true;
}
```

### Issue 2: Credentials Not Sent

```javascript
// Cookies won't be sent without credentials: 'include'
fetch('https://api.example.com/user', {
    credentials: 'include', // MUST be included
    headers: {
        'Authorization': `Bearer ${token}`,
    },
});
```

Also ensure server allows credentials:

```php
// Server must respond with
// Access-Control-Allow-Credentials: true
->withHeader('Access-Control-Allow-Credentials', 'true')
```

### Issue 3: Custom Headers Blocked

```javascript
// This will preflight because of custom header
fetch('https://api.example.com/data', {
    headers: {
        'X-Custom-Header': 'value', // Triggers preflight
    },
});
```

**Solution:** Add to allowed headers:

```php
->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Custom-Header')
```

---

## Testing CORS

```php
<?php
declare(strict_types=1);
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class CorsTest extends TestCase
{
    public function test_cors_preflight_allowed_origin(): void
    {
        $response = $this->options('/api/data', [
            'Origin' => 'https://example.com',
        ]);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals(
            'https://example.com',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Methods'));
    }

    public function test_cors_preflight_disallowed_origin(): void
    {
        $response = $this->options('/api/data', [
            'Origin' => 'https://untrusted.com',
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_cors_headers_on_actual_request(): void
    {
        $response = $this->get('/api/data', [
            'Origin' => 'https://example.com',
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            'https://example.com',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
    }

    public function test_cors_credentials_header(): void
    {
        $response = $this->get('/api/user/profile', [
            'Origin' => 'https://app.example.com',
        ]);

        $this->assertEquals('true', $response->getHeaderLine('Access-Control-Allow-Credentials'));
    }

    public function test_cors_exposed_headers(): void
    {
        $response = $this->get('/api/posts', [
            'Origin' => 'https://example.com',
        ]);

        $exposed = $response->getHeaderLine('Access-Control-Expose-Headers');
        $this->assertStringContainsString('X-Total-Count', $exposed);
    }
}
```

---

## Browser DevTools Debugging

Check CORS issues in browser console:

```javascript
// In DevTools Console
fetch('https://api.example.com/data')
    .then(r => r.headers.forEach((v, k) => console.log(`${k}: ${v}`)))
    .catch(e => console.error('Error:', e.message));

// Check request headers
console.log(document.location.origin); // Your origin
```

Request/Response headers to inspect:
- `Origin` (request) - Your website's origin
- `Access-Control-Allow-Origin` (response) - Allowed origins
- `Access-Control-Allow-Methods` (response) - Allowed HTTP methods
- `Access-Control-Allow-Headers` (response) - Allowed request headers
- `Access-Control-Allow-Credentials` (response) - If credentials allowed

---

## Best Practices

1. **Never use wildcard with credentials** - Don't set both `*` and `allow_credentials: true`
2. **Be specific about origins** - List exactly which domains can access your API
3. **Handle OPTIONS requests** - Return 204 No Content for successful preflight
4. **Expose necessary headers** - Tell clients which response headers are safe to read
5. **Use HTTPS** - Always enforce HTTPS in production
6. **Document CORS policy** - Help API consumers understand your requirements
7. **Monitor CORS errors** - Log failed preflight requests to detect issues
8. **Keep allowed headers minimal** - Only allow headers you actually use
9. **Test all origins** - Test both allowed and disallowed origins

---

## Related Documentation

- [User-Guide: Middleware](../User-Guide/Middleware.md)
- [Best-Practices: API Design](../Best-Practices/API-Design.md)
- [Reference: HTTP Messages](../Reference/Http-Messages.md)
