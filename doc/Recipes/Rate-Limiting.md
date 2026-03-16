# Rate-Limiting Recipe

## Problem

How do I prevent API abuse by implementing rate limiting?

This guide covers middleware-based rate limiting strategies for protecting your API from abuse, with Redis support for distributed systems.

---

## Solution

Implement rate limiting as middleware that tracks requests per IP address or user, enforcing limits and returning HTTP 429 (Too Many Requests) when exceeded.

---

## Basic Rate Limiting Middleware

```php
<?php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class RateLimitMiddleware extends Middleware
{
    private int $maxRequests = 100;
    private int $windowSeconds = 60;
    private string $cachePrefix = 'rate_limit:';

    public function initialize(array $args): bool
    {
        // Allow configuration via route args
        $this->maxRequests = $args['max_requests'] ?? $this->maxRequests;
        $this->windowSeconds = $args['window_seconds'] ?? $this->windowSeconds;

        return true;
    }

    public function handle(array $args): bool
    {
        $identifier = $this->getIdentifier();
        $key = $this->cachePrefix . $identifier;

        // Get current request count
        $cache = cache();
        $count = (int)($cache->get($key) ?? 0);

        // Check if limit exceeded
        if ($count >= $this->maxRequests) {
            return $this->rateLimited($identifier);
        }

        // Increment counter
        $cache->set(
            $key,
            $count + 1,
            $this->windowSeconds
        );

        // Add rate limit headers to response
        $response = getResponse()
            ->withHeader('X-RateLimit-Limit', (string)$this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string)($this->maxRequests - $count - 1))
            ->withHeader('X-RateLimit-Reset', (string)(time() + $this->windowSeconds));

        response($response);

        return true;
    }

    private function getIdentifier(): string
    {
        // Use user ID if authenticated
        $user = $this->request->getAttribute('user') ?? [];
        if (!empty($user['id'])) {
            return 'user:' . $user['id'];
        }

        // Fall back to IP address
        return 'ip:' . $this->getClientIp();
    }

    private function getClientIp(): string
    {
        // Check for IP behind reverse proxy
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            $value = $_SERVER[$header] ?? null;
            if ($value) {
                // Extract first IP if comma-separated
                $ips = explode(',', $value);
                return trim($ips[0]);
            }
        }

        return '0.0.0.0';
    }

    private function rateLimited(string $identifier): bool
    {
        response(responseJson(
            [
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Try again later.',
            ],
            429
        ));

        return false;
    }
}
```

---

## Redis-Backed Rate Limiting

For distributed systems, use Redis with atomic operations:

```php
<?php
declare(strict_types=1);
namespace App\Middleware;

use Predis\Client as RedisClient;
use Spin\Core\Middleware;

class RedisRateLimitMiddleware extends Middleware
{
    private RedisClient $redis;
    private int $maxRequests = 100;
    private int $windowSeconds = 60;
    private string $keyPrefix = 'rl:';

    public function initialize(array $args): bool
    {
        $this->redis = new RedisClient([
            'scheme' => env('REDIS_SCHEME', 'tcp'),
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ]);

        $this->maxRequests = $args['max_requests'] ?? $this->maxRequests;
        $this->windowSeconds = $args['window_seconds'] ?? $this->windowSeconds;

        return true;
    }

    public function handle(array $args): bool
    {
        $identifier = $this->getIdentifier();
        $key = $this->keyPrefix . $identifier;

        try {
            // Use Redis INCR with expiration
            $count = (int)$this->redis->incr($key);

            // Set expiration on first increment
            if ($count === 1) {
                $this->redis->expire($key, $this->windowSeconds);
            }

            // Check if limit exceeded
            if ($count > $this->maxRequests) {
                return $this->rateLimited($identifier, $count);
            }

            // Add rate limit headers
            $remaining = max(0, $this->maxRequests - $count);
            $resetTime = time() + $this->redis->ttl($key);

            $response = getResponse()
                ->withHeader('X-RateLimit-Limit', (string)$this->maxRequests)
                ->withHeader('X-RateLimit-Remaining', (string)$remaining)
                ->withHeader('X-RateLimit-Reset', (string)$resetTime)
                ->withHeader('RateLimit-Limit', (string)$this->maxRequests)
                ->withHeader('RateLimit-Remaining', (string)$remaining)
                ->withHeader('RateLimit-Reset', (string)$resetTime);

            response($response);

            return true;

        } catch (\Exception $e) {
            logger()->error('Rate limit Redis error: ' . $e->getMessage());
            // Fail open: allow request if Redis is down
            return true;
        }
    }

    private function getIdentifier(): string
    {
        // Use user ID if authenticated
        $user = $this->request->getAttribute('user') ?? [];
        if (!empty($user['id'])) {
            return 'user:' . $user['id'];
        }

        return 'ip:' . $this->getClientIp();
    }

    private function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            $value = $_SERVER[$header] ?? null;
            if ($value) {
                $ips = explode(',', $value);
                return trim($ips[0]);
            }
        }

        return '0.0.0.0';
    }

    private function rateLimited(string $identifier, int $count): bool
    {
        response(responseJson(
            [
                'error' => 'Too Many Requests',
                'message' => "Rate limit exceeded. Requests: $count / {$this->maxRequests}",
            ],
            429
        ));

        return false;
    }
}
```

---

## Per-User Rate Limiting

Differentiate limits based on user tier:

```php
<?php
declare(strict_types=1);
namespace App\Services;

class RateLimitConfig
{
    private array $tiers = [
        'guest' => ['limit' => 10, 'window' => 60],
        'free' => ['limit' => 100, 'window' => 60],
        'basic' => ['limit' => 1000, 'window' => 60],
        'premium' => ['limit' => 10000, 'window' => 60],
        'enterprise' => ['limit' => null, 'window' => null], // Unlimited
    ];

    public function getLimit(string $tier): ?int
    {
        return $this->tiers[$tier]['limit'] ?? null;
    }

    public function getWindow(string $tier): ?int
    {
        return $this->tiers[$tier]['window'] ?? null;
    }

    public function getUserTier(int $userId): string
    {
        // Query user subscription/tier from database
        $user = db()->table('users')->find($userId);
        return $user['subscription_tier'] ?? 'guest';
    }
}
```

Usage in middleware:

```php
public function handle(array $args): bool
{
    $identifier = $this->getIdentifier();

    // Determine user tier
    $user = $this->request->getAttribute('user') ?? [];
    $tier = $this->getTierForUser($user);

    // Get tier-specific limits
    $config = new RateLimitConfig();
    $maxRequests = $config->getLimit($tier);
    $windowSeconds = $config->getWindow($tier);

    // Skip rate limiting for unlimited tiers
    if ($maxRequests === null) {
        return true;
    }

    // ... rest of rate limit logic
}

private function getTierForUser(array $user): string
{
    if (empty($user['id'])) {
        return 'guest';
    }

    $config = new RateLimitConfig();
    return $config->getUserTier($user['id']);
}
```

---

## Group-Scoped Limits

SPIN middleware applies at the **common** (global) or **group** level — not per route. To apply different rate limits to different endpoint sets, use separate groups each with their own `RateLimitMiddleware`:

```json
{
  "groups": [
    {
      "name": "Search",
      "prefix": "/api/v1/search",
      "before": ["\\App\\Middlewares\\RateLimitSearchMiddleware"],
      "routes": [
        { "methods": ["GET"], "path": "/", "handler": "\\App\\Controllers\\SearchController" }
      ]
    },
    {
      "name": "Messages",
      "prefix": "/api/v1/messages",
      "before": ["\\App\\Middlewares\\RateLimitMessagesMiddleware"],
      "routes": [
        { "methods": ["POST"], "path": "/", "handler": "\\App\\Controllers\\MessageController" }
      ]
    },
    {
      "name": "Downloads",
      "prefix": "/api/v1/download",
      "before": ["\\App\\Middlewares\\RateLimitDownloadMiddleware"],
      "routes": [
        { "methods": ["GET"], "path": "/", "handler": "\\App\\Controllers\\DownloadController" }
      ]
    }
  ]
}
```

Each middleware class reads its own limit values from configuration or hardcoded constants.

---

## Client Response Handling

```javascript
async function fetchWithRateLimit(url, options = {}) {
    const response = await fetch(url, options);

    // Extract rate limit headers
    const remaining = response.headers.get('X-RateLimit-Remaining');
    const limit = response.headers.get('X-RateLimit-Limit');
    const reset = response.headers.get('X-RateLimit-Reset');

    console.log(`Rate Limit: ${remaining}/${limit}`);
    console.log(`Resets at: ${new Date(reset * 1000)}`);

    if (response.status === 429) {
        const resetTime = parseInt(reset) * 1000;
        const waitMs = resetTime - Date.now();

        console.warn(`Rate limited. Retry after ${waitMs}ms`);

        // Implement exponential backoff
        await new Promise(resolve => setTimeout(resolve, waitMs + 1000));
        return fetchWithRateLimit(url, options); // Retry
    }

    return response;
}

// Usage
fetchWithRateLimit('/api/search?q=something')
    .then(r => r.json())
    .then(data => console.log(data));
```

---

## Testing Rate Limits

```php
<?php
declare(strict_types=1);
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class RateLimitTest extends TestCase
{
    public function test_rate_limit_blocks_after_threshold(): void
    {
        $ip = '127.0.0.1';

        // Make requests up to limit
        for ($i = 0; $i < 100; $i++) {
            $response = $this->get('/api/search', [], ['REMOTE_ADDR' => $ip]);
            $this->assertEquals(200, $response->getStatusCode());
        }

        // Next request should be blocked
        $response = $this->get('/api/search', [], ['REMOTE_ADDR' => $ip]);
        $this->assertEquals(429, $response->getStatusCode());
    }

    public function test_rate_limit_headers_present(): void
    {
        $response = $this->get('/api/search');

        $this->assertTrue($response->hasHeader('X-RateLimit-Limit'));
        $this->assertTrue($response->hasHeader('X-RateLimit-Remaining'));
        $this->assertTrue($response->hasHeader('X-RateLimit-Reset'));

        $limit = (int)$response->getHeaderLine('X-RateLimit-Limit');
        $remaining = (int)$response->getHeaderLine('X-RateLimit-Remaining');

        $this->assertGreaterThan(0, $limit);
        $this->assertLessThan($limit, $remaining);
    }

    public function test_rate_limit_resets_after_window(): void
    {
        $ip = '127.0.0.1';

        // Exhaust limit
        for ($i = 0; $i < 100; $i++) {
            $this->get('/api/search', [], ['REMOTE_ADDR' => $ip]);
        }

        // Should be blocked
        $response = $this->get('/api/search', [], ['REMOTE_ADDR' => $ip]);
        $this->assertEquals(429, $response->getStatusCode());

        // After window expires
        sleep(61); // Window is 60 seconds

        // Should be allowed
        $response = $this->get('/api/search', [], ['REMOTE_ADDR' => $ip]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_different_users_have_separate_limits(): void
    {
        // User 1 makes 100 requests
        for ($i = 0; $i < 100; $i++) {
            $this->get('/api/search', [], ['user_id' => 1]);
        }

        // User 1 is blocked
        $response = $this->get('/api/search', [], ['user_id' => 1]);
        $this->assertEquals(429, $response->getStatusCode());

        // User 2 can still make requests
        $response = $this->get('/api/search', [], ['user_id' => 2]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

---

## Configuration

```env
RATE_LIMIT_ENABLED=true
RATE_LIMIT_WINDOW_SECONDS=60
RATE_LIMIT_DEFAULT_REQUESTS=100
RATE_LIMIT_BACKEND=redis
REDIS_SCHEME=tcp
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_DB=0
```

---

## Best Practices

1. **Rate limit by identifier** - Use user ID for authenticated requests, IP for anonymous
2. **Use Redis for distributed systems** - File cache is single-server only
3. **Return proper HTTP status** - Always use 429 for rate limit responses
4. **Include rate limit headers** - Inform clients of remaining quota
5. **Implement tiered limits** - Premium users get higher limits
6. **Different limits per endpoint** - Protect expensive operations more
7. **Graceful degradation** - Fail open if rate limit service is unavailable
8. **Monitor and alert** - Track rate limit violations
9. **Whitelist trusted sources** - Skip limits for internal services
10. **Publish limits in docs** - Help developers plan API usage

---

## Related Documentation

- [User-Guide: Middleware](../User-Guide/Middleware.md)
- [Best-Practices: API Design](../Best-Practices/API-Design.md)
- [Reference: Cache Adapters](../Reference/Cache.md)
