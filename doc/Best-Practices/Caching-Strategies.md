# Caching Strategies

## Overview

Caching reduces latency, decreases database load, and improves user experience. This guide covers decision criteria for what to cache, when to cache, cache invalidation patterns, and adapter selection for different scenarios.

## When to Cache

### Cache-Worthy Scenarios

**Read-heavy endpoints** — queries run frequently but data changes rarely

```php
// Product catalog: millions of requests daily, updated hourly
public function handleGET(array $args): ResponseInterface
{
    $key = "products:all:page:{$args['page']}";

    if ($products = cache()->get($key)) {
        return responseJson($products);
    }

    $products = $this->products->paginate($args['page'] ?? 1, 50);
    cache()->set($key, $products, 3600); // 1 hour

    return responseJson($products);
}
```

**Expensive computations** — aggregations, transformations, calculations

```php
public function handleGET(array $args): ResponseInterface
{
    $key = "dashboard:metrics:{$args['user_id']}";

    if ($metrics = cache()->get($key)) {
        return responseJson($metrics);
    }

    // Expensive: multiple queries, calculations
    $metrics = $this->computeMetrics($args['user_id']);
    cache()->set($key, $metrics, 600); // 10 minutes

    return responseJson($metrics);
}
```

**External API calls** — network latency, rate limits, third-party availability

```php
public function handleGET(array $args): ResponseInterface
{
    $key = "weather:{$args['city']}";

    if ($weather = cache()->get($key)) {
        return responseJson($weather);
    }

    try {
        $weather = $this->weatherApi->getWeather($args['city']);
        cache()->set($key, $weather, 1800); // 30 minutes
    } catch (ApiException $e) {
        logger()->error('Weather API failed', ['exception' => $e]);
        // Degrade gracefully if API unavailable
        return responseJson(['error' => 'Weather unavailable'], 503);
    }

    return responseJson($weather);
}
```

**Database aggregations** — SUM, COUNT, AVG, GROUP BY on large datasets

```php
public function getProductStats(): array
{
    return cache()->remember('product_stats', 3600, function () {
        return $this->db->fetch('
            SELECT
                COUNT(*) as total_products,
                SUM(stock) as total_stock,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price
            FROM products
        ');
    });
}
```

### Do NOT Cache

**User-specific, frequently-updated data** — invalidation overhead exceeds benefit

```php
// BAD: too much variation, invalidation is complex
cache()->set("user:{$id}:profile", $profile, 3600);

// BETTER: fetch fresh, cache at HTTP level
response()->setHeader('Cache-Control', 'private, max-age=60');
return responseJson($profile);
```

**Tiny datasets** — memory and serialization overhead costs more than lookup

```php
// BAD: 10 rows, database lookup is faster
cache()->set('all_countries', $countries, 86400);

// GOOD: just query it
return responseJson($this->countries->findAll());
```

**Highly sensitive data** — passwords, tokens, PII

```php
// NEVER cache
cache()->set("password_reset_token:{$token}", $data); // Bad idea

// Use temporary storage with secure generation
$token = bin2hex(random_bytes(32));
// Store in database with expiration, not cache
```

## What to Cache

### Application-Level Cache

Cache business logic results, not HTTP responses:

```php
class RecommendationService
{
    public function getRecommendedProducts(int $userId): array
    {
        $key = "recommendations:{$userId}";

        // Return cached if available
        if ($recs = cache()->get($key)) {
            return $recs;
        }

        // Compute if missing
        $recs = $this->computeRecommendations($userId);
        cache()->set($key, $recs, 1800);

        return $recs;
    }

    private function computeRecommendations(int $userId): array
    {
        // Complex logic: fetch user history, similar users, product attributes, etc.
        // Result: sorted list of 10 product IDs
        return $this->algorithm->run($userId);
    }
}
```

### HTTP-Level Cache

Use HTTP headers to let browsers and CDNs cache responses:

```php
public function handleGET(array $args): ResponseInterface
{
    $product = $this->products->find($args['id']);

    // Immutable resources: long TTL (1 year is max per HTTP spec)
    if ($this->isImmutable($product)) {
        response()->setHeader('Cache-Control', 'public, max-age=31536000, immutable');
    }
    // Revalidated: short max-age, requires server check
    else {
        response()->setHeader('Cache-Control', 'public, max-age=300');
        response()->setHeader('ETag', hash('md5', json_encode($product)));
    }

    // Client conditionally requests
    if (getRequest()->getHeaderLine('If-None-Match') === hash('md5', json_encode($product))) {
        return response()->setStatusCode(304); // Not Modified
    }

    return responseJson($product);
}
```

### Query Result Cache

Cache database query results to avoid repeated round-trips:

```php
class CategoryRepository
{
    public function findWithProducts(int $id): Category
    {
        $key = "category_with_products:{$id}";

        if ($category = cache()->get($key)) {
            return $category;
        }

        // Fetch category and eager-load products
        $category = $this->db->fetch('
            SELECT c.*, p.id as product_id, p.name as product_name
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            WHERE c.id = ?
        ', [$id]);

        cache()->set($key, $category, 3600);
        return $category;
    }
}
```

## Cache Invalidation Patterns

The hardest problem in caching: knowing when cached data is stale.

### Time-Based Expiration (TTL)

Set expiration based on how quickly data changes:

```php
// Configuration: changes rarely, long TTL
cache()->set('app_config', $config, 86400 * 7); // 7 days

// Product list: changes daily, medium TTL
cache()->set('products:featured', $products, 3600); // 1 hour

// User counts: changes by second, short TTL
cache()->set('online_users', $count, 60); // 1 minute

// Weather: external source, updates every 30 min
cache()->set("weather:{$city}", $weather, 1800); // 30 minutes
```

### Event-Based Invalidation

Delete cache when data changes:

```php
class ProductService
{
    public function update(int $id, array $data): Product
    {
        $product = $this->products->update($id, $data);

        // Invalidate caches affected by this product
        cache()->delete("product:{$id}");
        cache()->delete("products:featured");
        cache()->delete("category_stats:{$product->category_id}");

        return $product;
    }

    public function delete(int $id): void
    {
        $product = $this->products->find($id);
        $this->products->delete($id);

        cache()->delete("product:{$id}");
        cache()->delete("category_stats:{$product->category_id}");
    }
}
```

### Tag-Based Invalidation

Some caches (Redis, APCu) support tags to invalidate groups:

```php
// Cache with tags
cache()->tags(['products', 'featured'])->set('featured_list', $products, 3600);
cache()->tags(['products', 'category_5'])->set('category_5_products', $products, 3600);

// Invalidate by tag
cache()->tags(['featured'])->flush(); // Clears all tagged with 'featured'
cache()->tags(['category_5'])->flush(); // Clears all tagged with 'category_5'
```

### Cache-Aside (Lazy Loading)

Most common pattern: check cache, load from source if missing:

```php
public function getUser(int $id): ?User
{
    $key = "user:{$id}";

    // Check cache
    if ($cached = cache()->get($key)) {
        return $cached;
    }

    // Load from source
    if (!$user = $this->db->fetch('SELECT * FROM users WHERE id = ?', [$id])) {
        return null;
    }

    // Store in cache
    cache()->set($key, $user, 3600);

    return $user;
}
```

Or use `remember()` for brevity:

```php
public function getUser(int $id): ?User
{
    return cache()->remember("user:{$id}", 3600, fn() =>
        $this->db->fetch('SELECT * FROM users WHERE id = ?', [$id])
    );
}
```

### Write-Through Caching

Update cache and database together:

```php
public function updateUser(int $id, array $data): User
{
    $user = $this->db->update('users', $data, ['id' => $id]);

    // Also update cache
    cache()->set("user:{$id}", $user, 3600);

    return $user;
}
```

### Write-Behind Caching

Write to cache immediately, persist to database asynchronously:

```php
public function recordPageView(int $userId, string $page): void
{
    // Fast: write to cache immediately
    $key = "user_views:{$userId}";
    $views = cache()->get($key) ?? [];
    $views[] = ['page' => $page, 'timestamp' => time()];
    cache()->set($key, $views, 3600);

    // Async: persist to database (via queue)
    queue('record_page_view', ['user_id' => $userId, 'page' => $page]);
}
```

## Adapter Selection

Choose adapters based on deployment model and requirements:

### APCu (In-Process Memory)

```php
// config-dev.json
{
  "cache": {
    "default": "apcu",
    "adapters": {
      "apcu": {
        "driver": "apcu"
      }
    }
  }
}
```

**When to use:**
- Single-server deployments
- Development environments
- Per-process caching (not shared across workers)
- Extremely high-speed caching needs

**Advantages:** Zero network latency, no serialization
**Disadvantages:** Not shared across processes/servers, lost on restart

### Redis (Distributed Caching)

```php
// config-prod.json
{
  "cache": {
    "default": "redis",
    "adapters": {
      "redis": {
        "driver": "redis",
        "host": "redis.internal",
        "port": 6379,
        "database": 0
      }
    }
  }
}
```

**When to use:**
- Multi-server deployments
- Shared cache across services
- Advanced features (tags, TTL, atomic operations)
- High-traffic applications

**Advantages:** Distributed, persistent, fast, featureful
**Disadvantages:** Network latency, operational overhead

### File-Based Caching

```php
// config-dev.json
{
  "cache": {
    "default": "file",
    "adapters": {
      "file": {
        "driver": "file",
        "path": "storage/cache"
      }
    }
  }
}
```

**When to use:**
- Development
- Non-critical data
- Minimal dependencies
- Portable deployments

**Advantages:** No external dependencies, simple
**Disadvantages:** Slow (disk I/O), no distribution

## TTL Decision Matrix

| Data Type | Read Frequency | Change Frequency | Suggested TTL |
|-----------|-----------------|-----------------|---------------|
| User profile | High | Low | 1 hour |
| Product catalog | Very high | Medium | 30 minutes |
| Configuration | Medium | Very low | 24 hours |
| Dashboard metrics | High | Medium | 10 minutes |
| External API data | Medium | Unknown | 30 minutes |
| User sessions | Medium | Medium | 24 hours |
| Search results | High | Low | 1 hour |
| Real-time stats | Very high | Very high | 1 minute |

## Monitoring Cache Health

```php
class CacheHealthMiddleware extends Middleware
{
    public function handle(array $args): bool
    {
        $stats = cache()->getStats();
        if ($stats['hit_ratio'] < 0.5) {
            logger()->warning('Low cache hit ratio', $stats);
        }
        return true;
    }
}
```

## Key Takeaways

1. **Cache read-heavy, expensive, or external data** — measure first
2. **Choose TTL based on data freshness needs** — not all cache lasts 24 hours
3. **Invalidate explicitly on writes** — don't rely only on TTL
4. **Use appropriate adapter** — APCu for single-server, Redis for multi-server
5. **Monitor cache performance** — hit ratios, memory usage
6. **Tag-based invalidation** — simpler than tracking individual keys
7. **Combine cache levels** — HTTP (browser) + application (server) + database (query)

---

**See also:** [Performance-Optimization.md](Performance-Optimization.md), [Database-Patterns.md](Database-Patterns.md), [User-Guide/Cache.md](../User-Guide/Cache.md)
