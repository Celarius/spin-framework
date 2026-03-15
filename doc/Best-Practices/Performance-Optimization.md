# Performance Optimization

## Overview

Performance directly impacts user experience and operating costs. This guide covers caching strategies, query optimization, middleware efficiency, and measurement techniques. The key principle: optimize based on data, not assumptions.

## Caching Strategies

### When to Cache

Cache is valuable for:

- **Read-heavy endpoints** — products list, user profiles, configuration
- **Expensive computations** — reports, analytics, recommendations
- **External API calls** — third-party data, weather, exchange rates
- **Database aggregations** — sums, counts, distinct values

**Not valuable for:**

- Frequently updated data (cache invalidation overhead exceeds benefit)
- User-specific data with complex invalidation rules
- Tiny datasets (memory cost > lookup cost)

### What to Cache

Cache at appropriate layers based on invalidation complexity:

**Application cache** — computations, aggregations, external API responses

```php
class ProductService
{
    public function getCategoryStats(int $categoryId): array
    {
        $key = "category_stats:{$categoryId}";

        // Try cache first
        if ($stats = cache()->get($key)) {
            return $stats;
        }

        // Compute if missing
        $stats = [
            'total_products' => $this->products->countByCategory($categoryId),
            'average_price' => $this->products->averagePriceByCategory($categoryId),
            'total_sales' => $this->products->totalSalesByCategory($categoryId)
        ];

        // Store for 1 hour
        cache()->set($key, $stats, 3600);

        return $stats;
    }
}
```

**HTTP cache** — immutable or rarely-changed responses (use Cache-Control headers)

```php
public function handleGET(array $args): ResponseInterface
{
    $product = $this->products->find($args['id']);

    // Browser caches for 24 hours; CDN caches for 7 days
    response()->setHeader('Cache-Control', 'public, max-age=86400, s-maxage=604800');
    response()->setHeader('ETag', hash('md5', json_encode($product)));

    return responseJson($product);
}
```

**Database query cache** — avoid re-running the same query

```php
class UserRepository
{
    public function find(int $id): ?User
    {
        $key = "user:{$id}";

        if ($user = cache()->get($key)) {
            return $user;
        }

        $user = $this->db->fetch('SELECT * FROM users WHERE id = ?', [$id]);
        if ($user) {
            cache()->set($key, $user, 3600);
        }

        return $user;
    }
}
```

### Adapter Selection

| Adapter | Use Case | Pros | Cons |
|---------|----------|------|------|
| **APCu** | Per-process in-memory cache | Extremely fast, no serialization | Not shared across processes, no network |
| **Redis** | Distributed cache | Fast, shared across servers, advanced features | Network latency, operational overhead |
| **File** | Development, non-critical data | No dependencies, portable | Slow, no distribution |

### TTL Strategies

Set expiration based on data freshness requirements and invalidation patterns:

```php
// Static content: long TTL
cache()->set("app_config", $config, 86400 * 7); // 1 week

// User-generated content: shorter TTL
cache()->set("post:{$postId}", $post, 3600); // 1 hour

// Real-time data: minimal TTL
cache()->set("online_users", $count, 60); // 1 minute

// Invalidate explicitly when data changes
$post = $this->update($postId, $data);
cache()->delete("post:{$postId}"); // or set to null
```

## Database Query Optimization

### N+1 Problem

Classic mistake: fetching parent records in a loop triggers a query per child:

```php
// BAD: N+1 query problem
$users = $this->users->findAll();
foreach ($users as $user) {
    echo $user->name . ": " . $user->profile->bio; // Triggers query per user
}
```

**Solutions:**

1. **Eager load relationships** — fetch related data in one query

```php
$users = $this->users->findAllWithProfiles();
// Single query with JOIN
```

2. **Batch fetch** — get IDs, fetch all related data at once

```php
$users = $this->users->findAll();
$profilesByUserId = $this->profiles->findByUserIds(
    array_column($users, 'id')
);
```

3. **Lazy load on demand** — only if needed

```php
public function getProfile(int $userId): Profile
{
    return cache()->remember("profile:{$userId}", 3600, fn() =>
        $this->profiles->findByUserId($userId)
    );
}
```

### Query Patterns

**Pagination for large results:**

```php
$page = (int) (getRequest()->getQueryParams()['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$items = $this->db->fetch(
    'SELECT * FROM items ORDER BY created_at DESC LIMIT ? OFFSET ?',
    [$limit, $offset + 1] // +1 to detect if more pages exist
);

return responseJson([
    'items' => array_slice($items, 0, $limit),
    'has_next' => count($items) > $limit
]);
```

**Index critical columns:**

```sql
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
```

## Middleware Efficiency

Middleware runs for every request. Expensive operations here block all requests:

```php
// BAD: database query in global middleware
class RoleCheckMiddleware extends Middleware
{
    public function handle(array $args): bool
    {
        $roles = $this->db->fetch('SELECT * FROM roles'); // Every request!
        return true;
    }
}
```

**Better approach:**

```php
class RoleCheckMiddleware extends Middleware
{
    public function handle(array $args): bool
    {
        // Cache roles with long TTL, invalidate only on role changes
        $roles = cache()->remember('all_roles', 86400, fn() =>
            $this->db->fetch('SELECT * FROM roles')
        );
        return true;
    }
}
```

Or move the check to controllers where it's actually needed:

```php
class AdminController extends Controller
{
    public function __construct(private RoleService $roles) {}

    public function handleGET(array $args): ResponseInterface
    {
        if (!$this->roles->userHasRole(auth()->id(), 'admin')) {
            return responseJson(['error' => 'Forbidden'], 403);
        }
        // ...
    }
}
```

## Monitoring Performance

### Log Request Performance

Add middleware to track slow requests:

```php
class PerformanceMiddleware extends Middleware
{
    public function handle(array $args): bool
    {
        $start = microtime(true);
        register_shutdown_function(function () use ($start) {
            $duration = (microtime(true) - $start) * 1000;
            if ($duration > 500) { // Log if > 500ms
                logger()->warning('Slow request', [
                    'path' => getRequest()->getUri()->getPath(),
                    'method' => getRequest()->getMethod(),
                    'duration_ms' => round($duration, 2)
                ]);
            }
        });
        return true;
    }
}
```

### Database Query Logging

Monitor which queries consume time:

```php
class ConnectionManager
{
    public function execute(string $sql, array $params = []): void
    {
        $start = microtime(true);
        try {
            $this->conn->execute($sql, $params);
        } finally {
            $duration = (microtime(true) - $start) * 1000;
            if ($duration > 100) {
                logger()->debug('Slow query', [
                    'sql' => $sql,
                    'duration_ms' => round($duration, 2)
                ]);
            }
        }
    }
}
```

### Load Testing

Use tools like Apache Bench or wrk to simulate traffic:

```bash
# 100 requests, 10 concurrent
ab -n 100 -c 10 https://example.com/api/products

# 30 seconds with 4 threads
wrk -t4 -c100 -d30s https://example.com/api/products
```

Watch response times degrade under load to identify bottlenecks.

## Key Takeaways

1. **Cache at the right layer** — application (logic), HTTP (headers), or database (queries)
2. **Avoid N+1 queries** — eager load, batch fetch, or lazy load with caching
3. **Choose TTL based on freshness needs** — longer for static, shorter for volatile data
4. **Keep middleware fast** — move expensive logic to controllers or cache results
5. **Monitor actual performance** — log slow requests and queries, load test regularly
6. **Measure before optimizing** — profile first, optimize second
7. **Prefer simplicity over premature optimization** — cache when evidence supports it

---

**See also:** [Caching-Strategies.md](Caching-Strategies.md), [Database-Patterns.md](Database-Patterns.md), [User-Guide/Cache.md](../User-Guide/Cache.md)
