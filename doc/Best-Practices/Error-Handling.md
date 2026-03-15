# Error Handling

## Overview

Robust error handling ensures applications fail gracefully, log problems for debugging, and present appropriate responses to clients. This guide covers exception hierarchies, controller error handling, JSON responses, and logging strategies.

## Exception Hierarchy

SPIN provides base exception classes in `Spin\Exceptions\`. Build your own hierarchy for domain-specific errors:

```php
// Domain exceptions
namespace App\Exceptions;

class ApplicationException extends \Exception {}

class ValidationException extends ApplicationException
{
    public function __construct(
        public readonly array $errors,
        string $message = "Validation failed"
    ) {
        parent::__construct($message);
    }
}

class ResourceNotFoundException extends ApplicationException {}
class UnauthorizedException extends ApplicationException {}
class ConflictException extends ApplicationException {}
```

### Categorize by Intent

- **Validation errors** — input doesn't meet requirements (4xx)
- **Not found** — requested resource doesn't exist (404)
- **Authorization** — user lacks permission (403)
- **Authentication** — user not logged in (401)
- **Conflict** — business rule violated (409)
- **Server errors** — unexpected failure (5xx)

## Exception Handling in Controllers

Catch domain exceptions at the controller level and translate them to HTTP responses:

```php
class UserController extends Controller
{
    public function __construct(private UserService $users) {}

    public function handlePOST(array $args): ResponseInterface
    {
        try {
            $data = getRequest()->getParsedBody();
            $user = $this->users->create($data);
            return responseJson(['id' => $user->getId()], 201);
        } catch (ValidationException $e) {
            return responseJson(
                ['error' => $e->getMessage(), 'fields' => $e->errors],
                400
            );
        } catch (UserAlreadyExistsException $e) {
            return responseJson(['error' => 'Email already registered'], 409);
        } catch (ApplicationException $e) {
            logger()->warning('App error: ' . $e->getMessage());
            return responseJson(['error' => 'Operation failed'], 400);
        } catch (\Throwable $e) {
            logger()->error('Unexpected error', ['exception' => $e]);
            return responseJson(['error' => 'Internal server error'], 500);
        }
    }
}
```

### Middleware for Global Handling

Use error-handling middleware for cross-cutting concerns:

```php
class ErrorHandlerMiddleware extends Middleware
{
    public function handle(array $args): bool
    {
        try {
            return true; // Continue pipeline
        } catch (AuthenticationException $e) {
            return $this->respond(401, ['error' => 'Unauthorized']);
        } catch (ForbiddenException $e) {
            return $this->respond(403, ['error' => 'Forbidden']);
        }
    }

    private function respond(int $code, array $data): bool
    {
        response()->setStatusCode($code);
        response()->getBody()->write(json_encode($data));
        response()->setHeader('Content-Type', 'application/json');
        return false; // Short-circuit
    }
}
```

## JSON Error Response Format

Standardize error responses so clients can reliably parse them:

```json
{
  "error": "Human-readable message",
  "code": "RESOURCE_NOT_FOUND",
  "details": {
    "resource": "User",
    "id": 123
  }
}
```

For validation, include per-field errors:

```json
{
  "error": "Validation failed",
  "code": "VALIDATION_ERROR",
  "fields": {
    "email": "Invalid email format",
    "password": "Must be at least 8 characters"
  }
}
```

Implement a response helper or exception method to standardize:

```php
class ValidationException extends ApplicationException
{
    public function toJson(): array
    {
        return [
            'error' => $this->getMessage(),
            'code' => 'VALIDATION_ERROR',
            'fields' => $this->errors
        ];
    }
}
```

## Logging Errors with Context

Always log exceptions with sufficient context for debugging. Use structured logging:

```php
catch (\Throwable $e) {
    logger()->error('User creation failed', [
        'exception' => $e,
        'email' => $data['email'] ?? null,
        'ip' => getRequest()->getServerParams()['REMOTE_ADDR'] ?? null,
        'user_id' => auth()->id() ?? null,
        'trace' => $e->getTraceAsString()
    ]);
}
```

### Log Levels

| Level | Use Case |
|-------|----------|
| **error** | Exceptions, failed operations, expected errors that users need to know about |
| **warning** | Degraded behavior, deprecated usage, security concerns (rate limiting, suspicious activity) |
| **info** | Business events (user registered, order placed, payment processed) |
| **debug** | Internal state, variable values, performance metrics (only in development) |

### What NOT to Log

Never log:

- **Passwords or tokens** — even hashed ones; use placeholders
- **Credit card numbers** — always
- **PII unnecessarily** — log user ID, not email
- **Entire request bodies** — sanitize sensitive fields

```php
// Good
logger()->info('Login attempt', [
    'user_id' => $user->getId(),
    'ip' => getRequest()->getServerParams()['REMOTE_ADDR']
]);

// Bad
logger()->info('Login', ['password' => '***', 'request_body' => $body]);
```

## User-Friendly vs. Internal Messages

Distinguish between messages shown to users and detailed logs:

```php
try {
    $user = $this->users->create($data);
} catch (DatabaseException $e) {
    // Internal log with technical details
    logger()->error('Database error during user creation', [
        'exception' => $e,
        'sql' => $e->getQuery(),
        'params' => $e->getParams()
    ]);

    // User gets generic message
    return responseJson(
        ['error' => 'Unable to save user. Please try again.'],
        500
    );
}
```

### User Messages

- Be brief and helpful
- Explain what went wrong (without exposing internals)
- Suggest next steps when possible

```
User message: "Email already registered. Try logging in or reset your password."
Log message: "INSERT INTO users failed: Duplicate entry 'user@example.com' for key 'email'"
```

## Error Recovery Strategies

### Retry Logic

For transient failures (network timeouts, temporary unavailability):

```php
$attempt = 0;
$maxRetries = 3;

while ($attempt < $maxRetries) {
    try {
        return $this->externalApi->fetch($resource);
    } catch (TimeoutException $e) {
        $attempt++;
        if ($attempt >= $maxRetries) {
            throw new TemporaryUnavailableException('Service temporarily unavailable');
        }
        sleep(2 ** $attempt); // Exponential backoff
    }
}
```

### Fallback Values

Gracefully degrade when non-critical data is unavailable:

```php
$recommendations = [];
try {
    $recommendations = $this->recommendationEngine->suggest($userId);
} catch (\Throwable $e) {
    logger()->warning('Recommendation engine failed', ['exception' => $e]);
    // Serve page without recommendations instead of failing
}

return responseJson(['user' => $user, 'recommendations' => $recommendations]);
```

## Key Takeaways

1. **Define a domain exception hierarchy** — make intent clear through exception types
2. **Catch at controllers** — translate domain exceptions to HTTP responses
3. **Use middleware for cross-cutting errors** — authentication, authorization, global handlers
4. **Standardize JSON error format** — clients need consistent structure
5. **Log with context** — exception, user, request details, not passwords
6. **User messages are different from logs** — hide internals, be helpful
7. **Distinguish transient from permanent failures** — retry vs. fail appropriately

---

**See also:** [Application-Design.md](Application-Design.md), [Testing-Patterns.md](Testing-Patterns.md), [User-Guide/Security.md](../User-Guide/Security.md)
