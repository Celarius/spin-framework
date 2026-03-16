# Authentication Recipe

## Problem

How do I authenticate users in my SPIN Framework application?

This guide covers implementing robust user authentication using JWT tokens, session-based alternatives, and middleware patterns for protecting routes.

---

## Solution

SPIN Framework provides the building blocks for multiple authentication strategies. The most common approaches are:
1. **JWT Token Authentication** (stateless, ideal for APIs)
2. **Session-Based Authentication** (stateful, traditional web apps)
3. **Middleware Protection** (protecting routes and resources)

---

## JWT Token Implementation

### 1. Generate Authentication Tokens

The framework includes JWT helpers for token generation:

```php
<?php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Spin\Helpers\JWT;
use Psr\Http\Message\ResponseInterface;

class AuthController extends Controller
{
    public function handlePOST(array $args): ResponseInterface
    {
        $email = $this->getRequest()->getParsedBody()['email'] ?? null;
        $password = $this->getRequest()->getParsedBody()['password'] ?? null;

        // Validate credentials against your user database
        $user = $this->validateCredentials($email, $password);
        if (!$user) {
            return responseJson(['error' => 'Invalid credentials'], 401);
        }

        // Generate JWT token
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'roles' => $user['roles'] ?? ['user'],
        ];

        $token = JWT::encode(
            $payload,
            env('JWT_SECRET', 'your-secret-key'),
            'HS256'
        );

        return responseJson([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);
    }

    private function validateCredentials(string $email, string $password): ?array
    {
        // Query your users table
        $user = db()->table('users')->where('email', $email)->first();
        if (!$user) {
            return null;
        }

        // Verify password hash
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }
}
```

### 2. Validate Tokens in Middleware

Create an authentication middleware to validate incoming JWT tokens:

```php
<?php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;
use Spin\Helpers\JWT;

class AuthMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $authHeader = $request->getHeaderLine('Authorization') ?? '';

        // Extract token from "Bearer <token>"
        if (!preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            return $this->unauthorized('Missing or invalid Authorization header');
        }

        $token = $matches[1];
        $secret = env('JWT_SECRET', 'your-secret-key');

        try {
            $payload = JWT::decode($token, $secret, ['HS256']);

            // Store authenticated user in request attributes
            $this->request->setAttribute('user_id', $payload['user_id'] ?? null);
            $this->request->setAttribute('user', (array)$payload);

            return true;
        } catch (\Exception $e) {
            return $this->unauthorized('Invalid or expired token: ' . $e->getMessage());
        }
    }

    private function unauthorized(string $message): bool
    {
        $response = responseJson(['error' => $message], 401);
        $response = $response->withHeader('WWW-Authenticate', 'Bearer realm="API"');

        // Short-circuit pipeline
        response($response);
        return false;
    }
}
```

### 3. Refresh Token Pattern

Implement token refresh for better security:

```php
<?php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Spin\Helpers\JWT;
use Psr\Http\Message\ResponseInterface;

class RefreshTokenController extends Controller
{
    public function handlePOST(array $args): ResponseInterface
    {
        $body = $this->getRequest()->getParsedBody();
        $refreshToken = $body['refresh_token'] ?? null;

        if (!$refreshToken) {
            return responseJson(['error' => 'Refresh token required'], 400);
        }

        $secret = env('JWT_SECRET', 'your-secret-key');
        $refreshSecret = env('JWT_REFRESH_SECRET', 'your-refresh-secret');

        try {
            $payload = JWT::decode($refreshToken, $refreshSecret, ['HS256']);

            // Verify token type
            if (($payload['type'] ?? null) !== 'refresh') {
                throw new \Exception('Invalid token type');
            }

            // Generate new access token
            $newPayload = [
                'user_id' => $payload['user_id'],
                'email' => $payload['email'],
                'roles' => $payload['roles'] ?? ['user'],
                'type' => 'access',
            ];

            $newToken = JWT::encode($newPayload, $secret, 'HS256');

            return responseJson([
                'access_token' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]);
        } catch (\Exception $e) {
            return responseJson(['error' => 'Invalid refresh token'], 401);
        }
    }
}
```

---

## Session-Based Authentication

For traditional server-rendered applications, use PHP sessions:

```php
<?php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class SessionAuthMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        // Start session once
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return true;
    }

    public function handle(array $args): bool
    {
        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            // Redirect to login or return 401
            response(responseJson(['error' => 'Unauthenticated'], 401));
            return false;
        }

        // Load user data into request
        $this->request->setAttribute('user_id', $_SESSION['user_id']);
        $this->request->setAttribute('user', $_SESSION['user'] ?? []);

        return true;
    }
}
```

Login controller with session:

```php
<?php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class SessionLoginController extends Controller
{
    public function handlePOST(array $args): ResponseInterface
    {
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $this->getRequest()->getParsedBody()['email'] ?? null;
        $password = $this->getRequest()->getParsedBody()['password'] ?? null;

        $user = $this->validateCredentials($email, $password);
        if (!$user) {
            return responseJson(['error' => 'Invalid credentials'], 401);
        }

        // Store in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'roles' => $user['roles'] ?? ['user'],
        ];

        return responseJson(['message' => 'Logged in successfully']);
    }

    private function validateCredentials(string $email, string $password): ?array
    {
        $user = db()->table('users')->where('email', $email)->first();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }
}
```

---

## Route Configuration

Middleware in SPIN is applied at the **common** (global) level or **group** level — not per route. Use groups to scope authentication middleware to protected routes:

```json
{
  "common": {
    "before": [],
    "after": []
  },
  "groups": [
    {
      "name": "Public",
      "prefix": "/api/auth",
      "before": [],
      "routes": [
        { "methods": ["POST"], "path": "/login", "handler": "\\App\\Controllers\\AuthController" }
      ]
    },
    {
      "name": "Protected",
      "prefix": "/api/user",
      "before": ["\\App\\Middlewares\\AuthHttpBeforeMiddleware"],
      "routes": [
        { "methods": ["GET"], "path": "/profile", "handler": "\\App\\Controllers\\UserController" }
      ]
    },
    {
      "name": "Admin",
      "prefix": "/api/admin",
      "before": ["\\App\\Middlewares\\AuthHttpBeforeMiddleware", "\\App\\Middlewares\\AuthorizeMiddleware"],
      "routes": [
        { "methods": ["GET"], "path": "/users", "handler": "\\App\\Controllers\\AdminController" }
      ]
    }
  ]
}
```

---

## Authorization Middleware

Extend authentication with role-based authorization:

```php
<?php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class AuthorizeMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        $this->requiredRoles = $args['roles'] ?? [];
        return true;
    }

    public function handle(array $args): bool
    {
        $user = $this->request->getAttribute('user') ?? [];
        $userRoles = $user['roles'] ?? [];

        // Check if user has required roles
        $hasRole = array_intersect($userRoles, $this->requiredRoles);
        if (empty($hasRole) && !empty($this->requiredRoles)) {
            response(responseJson(['error' => 'Insufficient permissions'], 403));
            return false;
        }

        return true;
    }
}
```

---

## Client Integration

### Token Storage Options

```javascript
// Option 1: LocalStorage (vulnerable to XSS, suitable for public apps)
localStorage.setItem('access_token', token);
const token = localStorage.getItem('access_token');

// Option 2: SessionStorage (cleared on browser close)
sessionStorage.setItem('access_token', token);

// Option 3: HttpOnly Cookies (secure, protected from XSS)
// Set via Set-Cookie header from server
// Automatically sent with requests
```

### Sending Tokens

```javascript
// Authorization header
fetch('/api/user/profile', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
    },
});

// Or with Axios
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Handle 401 responses
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Token expired, refresh or redirect to login
            refreshToken().then(newToken => {
                localStorage.setItem('access_token', newToken);
                // Retry original request
            });
        }
        return Promise.reject(error);
    }
);
```

---

## Testing Authentication

```php
<?php
declare(strict_types=1);
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Spin\Helpers\JWT;

class AuthenticationTest extends TestCase
{
    private string $testSecret = 'test-secret-key';

    public function test_login_returns_token(): void
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('access_token', $body);
        $this->assertEquals('Bearer', $body['token_type']);
    }

    public function test_protected_route_requires_token(): void
    {
        $response = $this->get('/api/user/profile');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_valid_token_grants_access(): void
    {
        $token = JWT::encode(
            ['user_id' => 1, 'email' => 'user@example.com'],
            $this->testSecret,
            'HS256'
        );

        $response = $this->get('/api/user/profile', [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_invalid_token_returns_401(): void
    {
        $response = $this->get('/api/user/profile', [
            'Authorization' => 'Bearer invalid.token.here',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_expired_token_returns_401(): void
    {
        $expiredToken = JWT::encode(
            [
                'user_id' => 1,
                'exp' => time() - 3600, // Expired
            ],
            $this->testSecret,
            'HS256'
        );

        $response = $this->get('/api/user/profile', [
            'Authorization' => "Bearer {$expiredToken}",
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }
}
```

---

## Configuration Example

Store secrets in environment variables:

```env
JWT_SECRET=your-very-long-secret-key-with-good-entropy-min-32-chars
JWT_REFRESH_SECRET=your-refresh-secret-key-different-from-access
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600
JWT_REFRESH_EXPIRATION=604800
SESSION_TIMEOUT=1800
```

---

## Best Practices

1. **Use HTTPS only** - Never transmit tokens over unencrypted connections
2. **Secure token storage** - Use HttpOnly cookies when possible
3. **Short expiration** - Keep access token TTL short (15-60 minutes)
4. **Refresh tokens** - Use separate refresh tokens with longer TTL
5. **Validate always** - Check tokens on every protected request
6. **Hash passwords** - Use `password_hash()` and `password_verify()`
7. **Rate limit auth** - Prevent brute force attacks on login endpoints
8. **Log security events** - Track failed auth attempts

---

## Related Documentation

- [User-Guide: Middleware](../User-Guide/Middleware.md)
- [User-Guide: Controllers](../User-Guide/Controllers.md)
- [Best-Practices: Security](../Best-Practices/Security.md)
- [Reference: JWT Helper](../Reference/Helpers.md#jwt)
