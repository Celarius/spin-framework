# Security

SPIN Framework provides a robust security foundation with built-in authentication, authorization, and security middleware. This guide covers the security features and best practices for building secure SPIN applications.

## Authentication

SPIN supports multiple authentication methods through middleware and helper functions.

### HTTP Basic Authentication

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class BasicAuthMiddleware extends Middleware
{
    protected string $username;
    protected string $password;

    public function initialize(array $args): bool
    {
        $this->username = config('auth.basic.username');
        $this->password = config('auth.basic.password');
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Basic ')) {
            return $this->challenge();
        }

        $credentials = base64_decode(substr($authHeader, 6));
        [$username, $password] = explode(':', $credentials, 2);

        if ($username === $this->username && $password === $this->password) {
            container('user', ['username' => $username, 'authenticated' => true]);
            return true;
        }

        return $this->challenge();
    }

    private function challenge(): bool
    {
        response('', 401, [
            'WWW-Authenticate' => 'Basic realm="Secure Area"'
        ]);
        return false;
    }
}
```

### API Key Authentication

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class ApiKeyMiddleware extends Middleware
{
    protected array $validKeys;

    public function initialize(array $args): bool
    {
        $this->validKeys = config('auth.api_keys', []);
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $apiKey = $request->getHeaderLine('X-API-Key');

        if (empty($apiKey)) {
            return $this->unauthorized('API key required');
        }

        if (!in_array($apiKey, $this->validKeys)) {
            return $this->unauthorized('Invalid API key');
        }

        container('api_key', $apiKey);
        return true;
    }

    private function unauthorized(string $message): bool
    {
        responseJson(['error' => $message], 401);
        return false;
    }
}
```

### JWT Token Authentication

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;
use Spin\Helpers\JWT;

class JwtAuthMiddleware extends Middleware
{
    protected string $secret;

    public function initialize(array $args): bool
    {
        $this->secret = config('application.secret');
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Bearer token required');
        }

        $token = substr($authHeader, 7);

        try {
            $payload = JWT::decode($token, $this->secret, ['HS256']);
            
            if (is_null($payload)) {
                return $this->unauthorized('Invalid token');
            }

            // Check token expiration
            if (isset($payload->exp) && $payload->exp < time()) {
                return $this->unauthorized('Token expired');
            }

            // Store user data in container
            container('jwt:payload', $payload);
            container('user', [
                'id' => $payload->sub ?? null,
                'email' => $payload->email ?? null,
                'roles' => $payload->roles ?? []
            ]);

            return true;

        } catch (\Exception $e) {
            logger()->error('JWT validation failed', [
                'rid' => container('requestId'),
                'error' => $e->getMessage()
            ]);
            return $this->unauthorized('Invalid token');
        }
    }

    private function unauthorized(string $message): bool
    {
        responseJson(['error' => $message], 401);
        return false;
    }
}
```

## Authorization

### Role-Based Access Control

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class RoleMiddleware extends Middleware
{
    protected array $requiredRoles;

    public function initialize(array $args): bool
    {
        $this->requiredRoles = $args['roles'] ?? [];
        return true;
    }

    public function handle(array $args): bool
    {
        $user = container('user');

        if (!$user || !isset($user['roles'])) {
            return $this->forbidden('User not authenticated');
        }

        $userRoles = $user['roles'];
        $hasRequiredRole = false;

        foreach ($this->requiredRoles as $requiredRole) {
            if (in_array($requiredRole, $userRoles)) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            return $this->forbidden('Insufficient permissions');
        }

        return true;
    }

    private function forbidden(string $message): bool
    {
        responseJson(['error' => $message], 403);
        return false;
    }
}
```

### Resource Ownership

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class OwnershipMiddleware extends Middleware
{
    protected string $resourceType;

    public function initialize(array $args): bool
    {
        $this->resourceType = $args['resource'] ?? 'user';
        return true;
    }

    public function handle(array $args): bool
    {
        $user = container('user');
        $resourceId = $args['id'] ?? null;

        if (!$user || !$resourceId) {
            return $this->forbidden('Access denied');
        }

        // Check if user owns the resource
        if (!$this->userOwnsResource($user['id'], $resourceId, $this->resourceType)) {
            return $this->forbidden('Access denied');
        }

        return true;
    }

    private function userOwnsResource(int $userId, string $resourceId, string $resourceType): bool
    {
        // Implement resource ownership logic here
        // This is a simplified example
        return true;
    }

    private function forbidden(string $message): bool
    {
        responseJson(['error' => $message], 403);
        return false;
    }
}
```

## Input Validation

### Request Validation Middleware

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class ValidationMiddleware extends Middleware
{
    protected array $rules;

    public function initialize(array $args): bool
    {
        $this->rules = $args['rules'] ?? [];
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $data = $request->getParsedBody() ?? [];

        $errors = $this->validate($data, $this->rules);

        if (!empty($errors)) {
            responseJson(['errors' => $errors], 422);
            return false;
        }

        return true;
    }

    private function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            if (!$this->validateField($data[$field] ?? null, $rule)) {
                $errors[$field] = "Field {$field} is invalid";
            }
        }

        return $errors;
    }

    private function validateField($value, string $rule): bool
    {
        switch ($rule) {
            case 'required':
                return !empty($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'numeric':
                return is_numeric($value);
            default:
                return true;
        }
    }
}
```

### SQL Injection Prevention

SPIN uses PDO with prepared statements to prevent SQL injection:

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;

class UserController extends Controller
{
    public function handleGET(array $args)
    {
        $userId = $args['id'] ?? null;
        
        if (!$userId || !is_numeric($userId)) {
            return responseJson(['error' => 'Invalid user ID'], 400);
        }

        // Use prepared statements
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return responseJson(['error' => 'User not found'], 404);
        }

        return responseJson($user);
    }
}
```

## XSS Prevention

### Output Escaping

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;

class PageController extends Controller
{
    public function handleGET(array $args)
    {
        $userInput = $_GET['search'] ?? '';
        
        // Escape user input before output
        $escapedInput = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
        
        $model = [
            'search' => $escapedInput,
            'safe' => true
        ];

        $html = $this->engine->render('pages::search', $model);
        return response($html);
    }
}
```

### Content Security Policy

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class SecurityHeadersMiddleware extends Middleware
{
    public function handle(array $args): bool
    {
        $response = getResponse();
        
        // Content Security Policy
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';";
        $response = $response->withHeader('Content-Security-Policy', $csp);
        
        // Other security headers
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
        $response = $response->withHeader('X-Frame-Options', 'DENY');
        $response = $response->withHeader('X-XSS-Protection', '1; mode=block');
        $response = $response->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        return true;
    }
}
```

## CSRF Protection

### CSRF Token Middleware

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class CsrfMiddleware extends Middleware
{
    public function handle(array $args): bool
    {
        $request = getRequest();
        
        // Skip CSRF for GET requests
        if ($request->getMethod() === 'GET') {
            return true;
        }

        $token = $request->getParsedBody()['csrf_token'] ?? 
                 $request->getHeaderLine('X-CSRF-Token');

        $session = container('session');
        $expectedToken = $session['csrf_token'] ?? null;

        if (!$token || $token !== $expectedToken) {
            responseJson(['error' => 'CSRF token mismatch'], 403);
            return false;
        }

        return true;
    }
}
```

### CSRF Token Generation

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;

class FormController extends Controller
{
    public function handleGET(array $args)
    {
        // Generate CSRF token
        $csrfToken = bin2hex(random_bytes(32));
        
        // Store in session
        $session = container('session');
        $session['csrf_token'] = $csrfToken;
        container('session', $session);

        $model = ['csrf_token' => $csrfToken];
        $html = $this->engine->render('forms::create', $model);
        return response($html);
    }
}
```

## File Upload Security

### File Upload Validation

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class FileUploadMiddleware extends Middleware
{
    protected array $allowedTypes;
    protected int $maxSize;

    public function initialize(array $args): bool
    {
        $this->allowedTypes = config('uploads.allowed_types', ['jpg', 'png', 'pdf']);
        $this->maxSize = config('uploads.max_size', 5 * 1024 * 1024); // 5MB
        return true;
    }

    public function handle(array $args): bool
    {
        $files = getRequest()->getUploadedFiles();
        
        foreach ($files as $file) {
            if (!$this->validateFile($file)) {
                return false;
            }
        }

        return true;
    }

    private function validateFile($file): bool
    {
        // Check file size
        if ($file->getSize() > $this->maxSize) {
            responseJson(['error' => 'File too large'], 413);
            return false;
        }

        // Check file type
        $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            responseJson(['error' => 'File type not allowed'], 400);
            return false;
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getStream()->getMetadata('uri'));
        finfo_close($finfo);

        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf'
        ];

        if (!isset($allowedMimes[$extension]) || $allowedMimes[$extension] !== $mimeType) {
            responseJson(['error' => 'Invalid file content'], 400);
            return false;
        }

        return true;
    }
}
```

## Rate Limiting

### Rate Limiting Middleware

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class RateLimitMiddleware extends Middleware
{
    protected int $maxRequests;
    protected int $timeWindow;

    public function initialize(array $args): bool
    {
        $this->maxRequests = $args['max_requests'] ?? 100;
        $this->timeWindow = $args['time_window'] ?? 60; // seconds
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit:{$ip}";

        $current = cache()->get($key, 0);

        if ($current >= $this->maxRequests) {
            responseJson(['error' => 'Too many requests'], 429);
            return false;
        }

        // Increment request count
        cache()->set($key, $current + 1, $this->timeWindow);

        // Add rate limit headers
        $response = getResponse();
        $response = $response->withHeader('X-RateLimit-Limit', $this->maxRequests);
        $response = $response->withHeader('X-RateLimit-Remaining', $this->maxRequests - $current - 1);
        $response = $response->withHeader('X-RateLimit-Reset', time() + $this->timeWindow);

        return true;
    }
}
```

## Security Configuration

### Security Settings in Configuration

```json
{
  "security": {
    "csrf": {
      "enabled": true,
      "token_length": 32
    },
    "rate_limiting": {
      "enabled": true,
      "default_limit": 100,
      "default_window": 60
    },
    "file_uploads": {
      "max_size": 5242880,
      "allowed_types": ["jpg", "png", "pdf"],
      "scan_viruses": true
    },
    "headers": {
      "content_security_policy": "default-src 'self'",
      "x_frame_options": "DENY",
      "x_content_type_options": "nosniff"
    }
  }
}
```

## Security Best Practices

1. **Always Validate Input**: Never trust user input
2. **Use Prepared Statements**: Prevent SQL injection
3. **Escape Output**: Prevent XSS attacks
4. **Implement CSRF Protection**: For all state-changing operations
5. **Use HTTPS**: Encrypt all communications
6. **Implement Rate Limiting**: Prevent abuse
7. **Validate File Uploads**: Check type, size, and content
8. **Use Strong Authentication**: Implement proper auth mechanisms
9. **Log Security Events**: Monitor for suspicious activity
10. **Keep Dependencies Updated**: Patch security vulnerabilities

## Security Checklist

- [ ] Input validation implemented
- [ ] Output escaping implemented
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] CSRF protection
- [ ] File upload validation
- [ ] Authentication middleware
- [ ] Authorization checks
- [ ] Rate limiting
- [ ] Security headers
- [ ] HTTPS enforcement
- [ ] Error handling (no sensitive data exposure)
- [ ] Logging and monitoring
- [ ] Dependencies updated
- [ ] Security testing performed

## Security Testing

### Testing Authentication

```php
<?php
// tests/Security/AuthenticationTest.php

use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    public function testValidCredentials()
    {
        $middleware = new BasicAuthMiddleware();
        $args = ['username' => 'admin', 'password' => 'password'];
        
        $result = $middleware->handle($args);
        
        $this->assertTrue($result);
    }
    
    public function testInvalidCredentials()
    {
        $middleware = new BasicAuthMiddleware();
        $args = ['username' => 'admin', 'password' => 'wrong'];
        
        $result = $middleware->handle($args);
        
        $this->assertFalse($result);
    }
}
```

### Testing Authorization

```php
<?php
// tests/Security/AuthorizationTest.php

use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    public function testUserWithRequiredRole()
    {
        $middleware = new RoleMiddleware();
        $args = ['roles' => ['admin']];
        
        // Mock user with admin role
        container('user', ['roles' => ['admin', 'user']]);
        
        $result = $middleware->handle($args);
        
        $this->assertTrue($result);
    }
    
    public function testUserWithoutRequiredRole()
    {
        $middleware = new RoleMiddleware();
        $args = ['roles' => ['admin']];
        
        // Mock user without admin role
        container('user', ['roles' => ['user']]);
        
        $result = $middleware->handle($args);
        
        $this->assertFalse($result);
    }
}
```

This security guide provides a comprehensive foundation for building secure SPIN applications. Always follow security best practices and regularly audit your security measures.
