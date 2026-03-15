# API Versioning Recipe

## Problem

How do I version my API to support multiple client versions while maintaining backward compatibility?

This guide covers URL-based and header-based versioning strategies, route organization, and deprecation handling.

---

## Solution

Implement API versioning through URL prefixes or request headers, using route groups for clean organization and version-specific controllers.

---

## URL-Based Versioning

### Single Version Strategy

Organize routes by version prefix:

```json
{
  "common": {
    "prefix": "/api"
  },
  "groups": [
    {
      "prefix": "/v1",
      "routes": [
        {
          "path": "/users",
          "method": "GET",
          "controller": "v1/UserController",
          "handler": "handleGET"
        },
        {
          "path": "/users",
          "method": "POST",
          "controller": "v1/UserController",
          "handler": "handlePOST"
        },
        {
          "path": "/users/{id}",
          "method": "GET",
          "controller": "v1/UserController",
          "handler": "handleGET"
        }
      ]
    },
    {
      "prefix": "/v2",
      "routes": [
        {
          "path": "/users",
          "method": "GET",
          "controller": "v2/UserController",
          "handler": "handleGET"
        },
        {
          "path": "/users",
          "method": "POST",
          "controller": "v2/UserController",
          "handler": "handlePOST"
        }
      ]
    }
  ]
}
```

### V1 Controller (Legacy)

```php
<?php
declare(strict_types=1);
namespace App\Controllers\v1;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $userId = $args['id'] ?? null;

        if ($userId) {
            // Get single user
            $user = db()->table('users')->find($userId);
            if (!$user) {
                return responseJson(['error' => 'User not found'], 404);
            }

            // V1 response format
            return responseJson([
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'created' => $user['created_at'],
            ]);
        }

        // Get all users
        $users = db()->table('users')->get();

        return responseJson([
            'users' => array_map(function ($user) {
                return [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ];
            }, $users),
        ]);
    }

    public function handlePOST(array $args): ResponseInterface
    {
        $body = $this->getRequest()->getParsedBody();

        $user = db()->table('users')->insert([
            'name' => $body['name'] ?? null,
            'email' => $body['email'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return responseJson($user, 201);
    }
}
```

### V2 Controller (Enhanced)

```php
<?php
declare(strict_types=1);
namespace App\Controllers\v2;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $userId = $args['id'] ?? null;
        $includes = $this->parseIncludes();

        if ($userId) {
            // Get single user
            $user = db()->table('users')->find($userId);
            if (!$user) {
                return responseJson(['error' => 'User not found'], 404);
            }

            // V2 response format with more fields and relationships
            $response = [
                'data' => $this->formatUser($user, $includes),
            ];

            return responseJson($response);
        }

        // Get all users with pagination
        $page = (int)($this->getRequest()->getQueryParams()['page'] ?? 1);
        $perPage = (int)($this->getRequest()->getQueryParams()['per_page'] ?? 20);

        $query = db()->table('users');
        $total = $query->count();
        $users = $query->offset(($page - 1) * $perPage)
                       ->limit($perPage)
                       ->get();

        return responseJson([
            'data' => array_map(fn($u) => $this->formatUser($u, $includes), $users),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => ceil($total / $perPage),
            ],
        ]);
    }

    public function handlePOST(array $args): ResponseInterface
    {
        $body = $this->getRequest()->getParsedBody();

        // V2 validation
        $errors = [];
        if (empty($body['name'])) {
            $errors['name'] = 'Name is required';
        }
        if (empty($body['email']) || !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }

        if (!empty($errors)) {
            return responseJson([
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        $user = db()->table('users')->insert([
            'name' => $body['name'],
            'email' => $body['email'],
            'phone' => $body['phone'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return responseJson([
            'data' => $this->formatUser($user, []),
            'message' => 'User created successfully',
        ], 201);
    }

    private function formatUser(array $user, array $includes): array
    {
        $data = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? null,
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at'],
        ];

        // Include relationships if requested
        if (in_array('posts', $includes)) {
            $data['posts'] = db()->table('posts')
                                ->where('user_id', $user['id'])
                                ->get();
        }

        if (in_array('profile', $includes)) {
            $data['profile'] = db()->table('user_profiles')
                                   ->where('user_id', $user['id'])
                                   ->first();
        }

        return $data;
    }

    private function parseIncludes(): array
    {
        $includeParam = $this->getRequest()->getQueryParams()['include'] ?? '';
        if (empty($includeParam)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $includeParam)));
    }
}
```

---

## Header-Based Versioning

Use Accept header to specify API version:

```php
<?php
declare(straight_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class ApiVersionMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        return true;
    }

    public function handle(array $args): bool
    {
        $acceptHeader = $this->getRequest()->getHeaderLine('Accept');
        $version = $this->parseVersion($acceptHeader);

        // Store version in request for controller access
        $this->request->setAttribute('api_version', $version);

        // Add version to response
        $response = getResponse()
            ->withHeader('API-Version', $version);

        response($response);

        return true;
    }

    private function parseVersion(string $acceptHeader): string
    {
        // Parse version from Accept header
        // Example: application/vnd.myapi.v2+json

        if (preg_match('/vnd\.myapi\.v(\d+)/', $acceptHeader, $matches)) {
            return $matches[1];
        }

        // Alternative: Accept-Version header
        // Example: Accept-Version: v2

        $request = $this->getRequest();
        $versionHeader = $request->getHeaderLine('Accept-Version') ??
                        $request->getHeaderLine('API-Version') ?? '';

        if (!empty($versionHeader)) {
            return ltrim($versionHeader, 'v');
        }

        return '1'; // Default version
    }
}
```

Client sends header:

```bash
# Method 1: Accept header with vendor type
curl -H "Accept: application/vnd.myapi.v2+json" \
     https://api.example.com/users

# Method 2: Accept-Version header
curl -H "Accept-Version: v2" \
     https://api.example.com/users

# Method 3: API-Version header
curl -H "API-Version: 2" \
     https://api.example.com/users
```

---

## Version Adapter Pattern

Create adapters to handle multiple response formats:

```php
<?php
declare(strict_types=1);
namespace App\Adapters;

interface ApiResponseAdapterInterface
{
    public function formatUser(array $user): array;
    public function formatUserList(array $users, int $total, int $page, int $perPage): array;
}

class UserAdapterV1 implements ApiResponseAdapterInterface
{
    public function formatUser(array $user): array
    {
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'created' => $user['created_at'],
        ];
    }

    public function formatUserList(
        array $users,
        int $total,
        int $page,
        int $perPage
    ): array {
        return [
            'users' => array_map([$this, 'formatUser'], $users),
        ];
    }
}

class UserAdapterV2 implements ApiResponseAdapterInterface
{
    public function formatUser(array $user): array
    {
        return [
            'type' => 'user',
            'id' => $user['id'],
            'attributes' => [
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'] ?? null,
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
            ],
        ];
    }

    public function formatUserList(
        array $users,
        int $total,
        int $page,
        int $perPage
    ): array {
        return [
            'data' => array_map([$this, 'formatUser'], $users),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => ceil($total / $perPage),
            ],
        ];
    }
}
```

Use in unified controller:

```php
<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Adapters\UserAdapterV1;
use App\Adapters\UserAdapterV2;
use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $version = $this->getRequest()->getAttribute('api_version') ?? '1';
        $adapter = $this->getAdapter($version);

        $userId = $args['id'] ?? null;

        if ($userId) {
            $user = db()->table('users')->find($userId);
            return responseJson($adapter->formatUser($user));
        }

        $page = (int)($this->getRequest()->getQueryParams()['page'] ?? 1);
        $perPage = (int)($this->getRequest()->getQueryParams()['per_page'] ?? 20);

        $query = db()->table('users');
        $total = $query->count();
        $users = $query->offset(($page - 1) * $perPage)
                       ->limit($perPage)
                       ->get();

        return responseJson(
            $adapter->formatUserList($users, $total, $page, $perPage)
        );
    }

    private function getAdapter(string $version)
    {
        return match ($version) {
            '2' => new UserAdapterV2(),
            default => new UserAdapterV1(),
        };
    }
}
```

---

## Deprecation Strategy

Mark deprecated API versions:

```php
<?php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class DeprecationMiddleware extends Middleware
{
    private array $deprecatedVersions = [
        '1' => [
            'sunset_date' => '2027-01-01',
            'replacement' => 'v2',
            'message' => 'API v1 is deprecated. Please migrate to v2.',
        ],
    ];

    public function handle(array $args): bool
    {
        $version = $this->getRequest()->getAttribute('api_version') ?? '1';

        if (!isset($this->deprecatedVersions[$version])) {
            return true;
        }

        $deprecation = $this->deprecatedVersions[$version];

        // Add deprecation headers
        $response = getResponse()
            ->withHeader('Deprecation', 'true')
            ->withHeader('Sunset', 'Sun, ' . date('d M Y H:i:s O', strtotime($deprecation['sunset_date'])))
            ->withHeader('Warning', '299 - "' . $deprecation['message'] . '"');

        if (isset($deprecation['replacement'])) {
            $response = $response->withHeader('Link', '</' . $deprecation['replacement'] . '>; rel="successor-version"');
        }

        response($response);

        // Log deprecation usage
        logger()->info('Deprecated API version used', [
            'version' => $version,
            'client_ip' => $this->getClientIp(),
            'endpoint' => getRequest()->getUri()->getPath(),
        ]);

        return true;
    }

    private function getClientIp(): string
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }
}
```

Client sees deprecation warning:

```
HTTP/1.1 200 OK
Deprecation: true
Sunset: Sun, 01 Jan 2027 00:00:00 GMT
Warning: 299 - "API v1 is deprecated. Please migrate to v2."
Link: </v2>; rel="successor-version"
```

---

## Documentation

Document API versions in endpoints index:

```markdown
# API Versions

## Current Versions

| Version | Status | Sunset Date | Notes |
|---------|--------|-------------|-------|
| v2 | Current | - | Latest, recommended for new integrations |
| v1 | Deprecated | 2027-01-01 | Legacy, migrate to v2 |

## Migration Guide

See [Migrating from v1 to v2](../migration-v1-to-v2.md)

## Version History

- **v2** (2025-06-01): Added includes parameter, JSON:API format, pagination
- **v1** (2023-01-01): Initial release
```

---

## Testing Multiple Versions

```php
<?php
declare(strict_types=1);
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class ApiVersioningTest extends TestCase
{
    public function test_v1_user_format(): void
    {
        $response = $this->get('/api/v1/users/1');

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);

        // V1 format
        $this->assertArrayHasKey('id', $body);
        $this->assertArrayHasKey('name', $body);
        $this->assertArrayHasKey('created', $body); // v1 uses 'created'
        $this->assertArrayNotHasKey('updated_at', $body);
    }

    public function test_v2_user_format(): void
    {
        $response = $this->get('/api/v2/users/1');

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string)$response->getBody(), true);

        // V2 format
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('meta', $body);

        $user = $body['data'];
        $this->assertEquals('user', $user['type']);
        $this->assertArrayHasKey('attributes', $user);
        $this->assertArrayHasKey('created_at', $user['attributes']);
    }

    public function test_v1_returns_deprecation_header(): void
    {
        $response = $this->get('/api/v1/users');

        $this->assertTrue($response->hasHeader('Deprecation'));
        $this->assertEquals('true', $response->getHeaderLine('Deprecation'));
    }

    public function test_v2_has_pagination(): void
    {
        $response = $this->get('/api/v2/users?page=1&per_page=10');

        $body = json_decode((string)$response->getBody(), true);

        $this->assertArrayHasKey('meta', $body);
        $this->assertArrayHasKey('page', $body['meta']);
        $this->assertArrayHasKey('per_page', $body['meta']);
    }

    public function test_header_based_versioning(): void
    {
        $response = $this->get('/api/users', [
            'Accept-Version' => 'v2',
        ]);

        // Should return v2 format
        $body = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('data', $body);
    }
}
```

---

## Best Practices

1. **Plan for multiple versions** - Accept that you'll maintain 2-3 versions simultaneously
2. **Provide migration timeline** - Give clients 12+ months to migrate before sunset
3. **Use semantic versioning** - v1, v2, v3 (not v1.0, v1.1, v2.0)
4. **Document differences** - Maintain migration guides for each version
5. **Test all versions** - Ensure version-specific tests in your test suite
6. **Use consistent structure** - Even across versions, maintain similar endpoint structure
7. **Deprecation headers** - Use standard HTTP deprecation headers (RFC 8594)
8. **Monitor usage** - Track which API versions clients are using
9. **Support longer** - Enterprise customers need longer sunset periods
10. **Minimize versions** - Don't create new versions for minor changes

---

## Related Documentation

- [User-Guide: Routing](../User-Guide/Routing.md)
- [Best-Practices: API Design](../Best-Practices/API-Design.md)
- [Reference: HTTP Status Codes](../Reference/Http-Codes.md)
