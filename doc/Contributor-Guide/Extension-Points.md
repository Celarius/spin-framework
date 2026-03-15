# Extension Points — SPIN Framework

This document details how to extend the SPIN Framework with custom implementations for cache adapters, database drivers, middleware, and helpers.

## Creating Custom Cache Adapters

Cache adapters provide pluggable storage backends while maintaining the PSR-16 interface.

### Architecture

All adapters extend `Spin\Cache\Adapters\AbstractCacheAdapter`:

```php
abstract class AbstractCacheAdapter implements CacheInterface
{
    protected int $default_ttl = 0;

    abstract public function has(string $key): bool;
    abstract public function get(string $key, $default = null);
    abstract public function set(string $key, $value, $ttl = null): bool;
    abstract public function delete(string $key): bool;
    abstract public function clear(): bool;
    abstract public function getMultiple(iterable $keys, $default = null): iterable;
    abstract public function setMultiple(iterable $values, $ttl = null): bool;
    abstract public function deleteMultiple(iterable $keys): bool;
}
```

### Example: Memcached Adapter

```php
declare(strict_types=1);
namespace App\Cache\Adapters;

use Spin\Cache\Adapters\AbstractCacheAdapter;

class MemcachedAdapter extends AbstractCacheAdapter
{
    private \Memcached $client;

    public function __construct(array $config = [])
    {
        $this->client = new \Memcached();
        $this->client->addServers($config['servers'] ?? [['127.0.0.1', 11211]]);
        $this->default_ttl = $config['default_ttl'] ?? 0;
    }

    public function has(string $key): bool
    {
        return $this->client->get($key) !== false;
    }

    public function get(string $key, $default = null)
    {
        $value = $this->client->get($key);
        return $value !== false ? $value : $default;
    }

    public function set(string $key, $value, $ttl = null): bool
    {
        $ttl = $ttl ?? $this->default_ttl;
        return $this->client->set($key, $value, (int)$ttl);
    }

    public function delete(string $key): bool
    {
        return $this->client->delete($key);
    }

    public function clear(): bool
    {
        return $this->client->flush();
    }

    public function getMultiple(iterable $keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(iterable $values, $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            $success = $this->set($key, $value, $ttl) && $success;
        }
        return $success;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            $success = $this->delete($key) && $success;
        }
        return $success;
    }
}
```

### Configuration

Register in `config-{env}.json`:

```json
{
  "cache": {
    "adapter": "App\\Cache\\Adapters\\MemcachedAdapter",
    "default_ttl": 3600,
    "options": {
      "servers": [
        ["127.0.0.1", 11211],
        ["cache-2.local", 11211]
      ]
    }
  }
}
```

### Testing Custom Adapters

```php
declare(strict_types=1);
namespace Tests\Cache\Adapters;

use PHPUnit\Framework\TestCase;
use App\Cache\Adapters\MemcachedAdapter;

class MemcachedAdapterTest extends TestCase
{
    private MemcachedAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new MemcachedAdapter([
            'servers' => [['127.0.0.1', 11211]],
            'default_ttl' => 3600
        ]);
        $this->adapter->clear();
    }

    public function testSetAndGet(): void
    {
        $this->adapter->set('test_key', 'test_value');
        $this->assertEquals('test_value', $this->adapter->get('test_key'));
    }

    public function testHas(): void
    {
        $this->adapter->set('existing', 'value');
        $this->assertTrue($this->adapter->has('existing'));
        $this->assertFalse($this->adapter->has('nonexistent'));
    }

    public function testTtlExpiration(): void
    {
        $this->adapter->set('short_lived', 'value', 1);
        sleep(2);
        $this->assertNull($this->adapter->get('short_lived'));
    }
}
```

## Creating Custom Database Drivers

Database drivers provide PDO-based access to different database engines.

### Architecture

All drivers extend `Spin\Database\Drivers\Pdo\AbstractPdoDriver`:

```php
abstract class AbstractPdoDriver
{
    protected string $dsn_template;
    protected \PDO $pdo;
    protected array $config;

    abstract public function connect(): void;
    public function query(string $sql, array $params = []);
    public function disconnect(): void;
}
```

### Example: ClickHouse Driver

```php
declare(strict_types=1);
namespace App\Database\Drivers\Pdo;

use Spin\Database\Drivers\Pdo\AbstractPdoDriver;

class ClickHouseDriver extends AbstractPdoDriver
{
    protected string $dsn_template = 'http:host=%s;port=%d;dbname=%s';

    public function connect(): void
    {
        try {
            $host = $this->config['host'] ?? 'localhost';
            $port = $this->config['port'] ?? 8123;
            $database = $this->config['database'] ?? 'default';
            $user = $this->config['user'] ?? 'default';
            $password = $this->config['password'] ?? '';

            $dsn = sprintf($this->dsn_template, $host, $port, $database);

            $this->pdo = new \PDO(
                $dsn,
                $user,
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        } catch (\PDOException $e) {
            throw new \Exception("ClickHouse connection failed: " . $e->getMessage());
        }
    }
}
```

### Configuration

Register in `config-{env}.json`:

```json
{
  "database": {
    "pdo": {
      "clickhouse": {
        "driver": "App\\Database\\Drivers\\Pdo\\ClickHouseDriver",
        "host": "localhost",
        "port": 8123,
        "database": "default",
        "user": "default",
        "password": ""
      }
    }
  }
}
```

## Creating Custom Middleware

Middleware intercepts requests and responses in the execution pipeline.

### Architecture

All middleware extend `Spin\Core\Middleware`:

```php
abstract class Middleware
{
    public function initialize(array $args): bool  // Setup phase
    public function handle(array $args): bool       // Per-request phase
}
```

### Example: RateLimiting Middleware

```php
declare(strict_types=1);
namespace App\Middleware;

use Spin\Core\Middleware;

class RateLimitMiddleware extends Middleware
{
    private int $max_requests;
    private int $window_seconds;
    private \Spin\Cache\Adapters\AbstractCacheAdapter $cache;

    public function initialize(array $args): bool
    {
        $this->max_requests = $args['max_requests'] ?? 100;
        $this->window_seconds = $args['window_seconds'] ?? 60;
        $this->cache = cache();
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $client_ip = $request->getHeader('X-Forwarded-For')[0] ??
                     $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $key = "rate_limit:{$client_ip}";
        $current = (int)$this->cache->get($key, 0);

        if ($current >= $this->max_requests) {
            $response = response()
                ->withStatus(429)
                ->withHeader('Retry-After', (string)$this->window_seconds);

            $response->getBody()->write('Too many requests');
            return false; // Short-circuit pipeline
        }

        $this->cache->set($key, $current + 1, $this->window_seconds);
        return true; // Continue pipeline
    }
}
```

### Configuration

Register globally in `config-{env}.json`:

```json
{
  "middleware": {
    "global_before": [
      {
        "name": "App\\Middleware\\RateLimitMiddleware",
        "config": {
          "max_requests": 100,
          "window_seconds": 60
        }
      }
    ]
  }
}
```

Or per-route:

```json
{
  "routes": [
    {
      "path": "/api/action",
      "methods": ["POST"],
      "controller": "App\\Controllers\\ActionController",
      "middleware": [
        {
          "name": "App\\Middleware\\RateLimitMiddleware",
          "config": {
            "max_requests": 10,
            "window_seconds": 60
          }
        }
      ]
    }
  ]
}
```

### Testing Custom Middleware

```php
declare(strict_types=1);
namespace Tests\Middleware;

use PHPUnit\Framework\TestCase;
use App\Middleware\RateLimitMiddleware;

class RateLimitMiddlewareTest extends TestCase
{
    private RateLimitMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new RateLimitMiddleware();
        $this->middleware->initialize([
            'max_requests' => 3,
            'window_seconds' => 60
        ]);
        cache()->clear();
    }

    public function testAllowsRequestsWithinLimit(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $result = $this->middleware->handle([]);
            $this->assertTrue($result, "Request $i should be allowed");
        }
    }

    public function testBlocksRequestsOverLimit(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->middleware->handle([]);
        }

        $result = $this->middleware->handle([]);
        $this->assertFalse($result, "Fourth request should be blocked");
    }
}
```

## Creating Custom Helpers

Helpers provide convenient functions for common tasks.

### Implementation

Create a helper class in `src/Helpers/`:

```php
declare(strict_types=1);
namespace Spin\Helpers;

class StringHelper
{
    public static function slugify(string $text): string
    {
        return strtolower(
            preg_replace('/[^a-z0-9]+/', '-',
                preg_replace('/[^a-z0-9\s]/i', '', $text)
            )
        );
    }

    public static function truncate(string $text, int $length, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }
}
```

### Register as Global Helper

Add to `src/helpers.php` or equivalent:

```php
function slugify(string $text): string
{
    return \Spin\Helpers\StringHelper::slugify($text);
}

function truncate(string $text, int $length): string
{
    return \Spin\Helpers\StringHelper::truncate($text, $length);
}
```

## Testing Extensions

### Test Structure

```
tests/
├── Cache/
│   └── Adapters/
│       └── MemcachedAdapterTest.php
├── Database/
│   └── Drivers/
│       └── ClickHouseDriverTest.php
├── Middleware/
│   └── RateLimitMiddlewareTest.php
└── Helpers/
    └── StringHelperTest.php
```

### Coverage Expectations

- Minimum 85% coverage for new extensions
- Critical paths (connection, cache miss/hit) 100%
- Error handling and edge cases covered
- Integration tests with framework components

### Mock Framework Services

```php
class CustomAdapterTest extends TestCase
{
    private MockObject $mockLogger;

    protected function setUp(): void
    {
        $this->mockLogger = $this->createMock(LoggerInterface::class);
    }

    public function testLogsOnError(): void
    {
        $this->mockLogger
            ->expects($this->once())
            ->method('error');
        // ... test implementation
    }
}
```

## Extension Checklist

Before submitting an extension:

- [ ] Implements required interface/extends correct base class
- [ ] Follows PSR-4 namespace mapping
- [ ] All public methods documented with docblocks
- [ ] Type hints on all parameters and return values
- [ ] Strict types declared at top of file
- [ ] 85%+ test coverage
- [ ] Tests verify success and failure paths
- [ ] Configuration documented in comments
- [ ] No breaking changes to framework contracts
- [ ] Examples provided in docstrings
- [ ] README/documentation added to doc/

---

**Last Updated:** 2026-03-15
**Framework Version:** 0.0.36+
