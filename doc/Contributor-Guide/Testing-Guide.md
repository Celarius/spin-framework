# Testing Guide — SPIN Framework

This document covers writing tests for SPIN Framework changes and understanding the test infrastructure.

## Test Organization

Tests mirror the `src/` directory structure under `tests/`:

```
src/
├── Core/
│   ├── Controller.php
│   ├── Middleware.php
│   └── Config.php
├── Cache/
│   └── Adapters/
│       ├── AbstractCacheAdapter.php
│       ├── ApcuAdapter.php
│       └── RedisAdapter.php
└── Database/
    └── Drivers/
        └── Pdo/
            └── AbstractPdoDriver.php

tests/
├── CoreTest.php           # Application tests
├── Core/
│   ├── ControllerTest.php
│   ├── MiddlewareTest.php
│   └── ConfigTest.php
├── Cache/
│   └── Adapters/
│       ├── AbstractCacheAdapterTest.php
│       ├── ApcuAdapterTest.php
│       └── RedisAdapterTest.php
└── Database/
    └── Drivers/
        └── Pdo/
            └── AbstractPdoDriverTest.php
```

## Writing Unit Tests

### Basic Test Structure

```php
declare(strict_types=1);
namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Spin\Core\Config;

class ConfigTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        // Setup run before each test
        $this->config = new Config([
            'database' => [
                'host' => 'localhost',
                'port' => 5432
            ]
        ]);
    }

    protected function tearDown(): void
    {
        // Cleanup run after each test
    }

    public function testGetSimpleValue(): void
    {
        $this->assertEquals('localhost', $this->config->get('database.host'));
    }

    public function testGetDefaultValue(): void
    {
        $this->assertNull($this->config->get('nonexistent'));
        $this->assertEquals('default', $this->config->get('nonexistent', 'default'));
    }

    public function testThrowsOnInvalidPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->config->get('');
    }
}
```

### Assertions

Common assertions for SPIN Framework tests:

```php
// Equality
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual);  // Strict comparison

// Boolean/Null
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertNull($value);
$this->assertNotNull($value);

// Type checks
$this->assertIsArray($value);
$this->assertIsString($value);
$this->assertInstanceOf(ClassName::class, $value);

// Collections
$this->assertCount(5, $array);
$this->assertArrayHasKey('key', $array);
$this->assertContains($value, $array);

// Exceptions
$this->expectException(ExceptionClass::class);
$this->expectExceptionMessage('Error message');
// ... code that should throw
```

## Writing Controller Tests

Controllers require HTTP context mocking:

```php
declare(strict_types=1);
namespace Tests\App\Controllers;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use App\Controllers\UserController;

class UserControllerTest extends TestCase
{
    private UserController $controller;

    protected function setUp(): void
    {
        $this->controller = new UserController();

        // Mock request
        $request = new Request('GET', '/api/users/123');
        $GLOBALS['request'] = $request;

        // Mock response
        $response = new Response();
        $GLOBALS['response'] = $response;
    }

    public function testGetUserReturnsJsonResponse(): void
    {
        $response = $this->controller->handleGET(['id' => '123']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testGetUserNotFoundReturns404(): void
    {
        $response = $this->controller->handleGET(['id' => 'nonexistent']);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
```

## Writing Middleware Tests

Middleware tests verify pipeline behavior:

```php
declare(strict_types=1);
namespace Tests\App\Middleware;

use PHPUnit\Framework\TestCase;
use App\Middleware\AuthenticationMiddleware;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;

class AuthenticationMiddlewareTest extends TestCase
{
    private AuthenticationMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new AuthenticationMiddleware();
        $this->middleware->initialize([]);
    }

    public function testAllowsRequestWithValidToken(): void
    {
        $request = new Request('GET', '/api/protected');
        $request = $request->withHeader('Authorization', 'Bearer valid_token');
        $GLOBALS['request'] = $request;

        $result = $this->middleware->handle([]);

        $this->assertTrue($result, 'Middleware should continue pipeline');
    }

    public function testBlocksRequestWithoutToken(): void
    {
        $request = new Request('GET', '/api/protected');
        $GLOBALS['request'] = $request;

        $result = $this->middleware->handle([]);

        $this->assertFalse($result, 'Middleware should short-circuit');
    }

    public function testRejectsInvalidToken(): void
    {
        $request = new Request('GET', '/api/protected');
        $request = $request->withHeader('Authorization', 'Bearer invalid');
        $GLOBALS['request'] = $request;

        $result = $this->middleware->handle([]);

        $this->assertFalse($result);
    }
}
```

## Writing Cache Adapter Tests

Cache adapters need PSR-16 compliance verification:

```php
declare(strict_types=1);
namespace Tests\Cache\Adapters;

use PHPUnit\Framework\TestCase;
use Spin\Cache\Adapters\RedisAdapter;

class RedisAdapterTest extends TestCase
{
    private RedisAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new RedisAdapter([
            'host' => 'localhost',
            'port' => 6379,
            'database' => 15,  // Use separate database for tests
            'default_ttl' => 3600
        ]);

        // Clear test database before each test
        $this->adapter->clear();
    }

    protected function tearDown(): void
    {
        $this->adapter->clear();
    }

    public function testSetAndGet(): void
    {
        $this->adapter->set('test_key', 'test_value');
        $this->assertEquals('test_value', $this->adapter->get('test_key'));
    }

    public function testGetReturnsDefaultWhenKeyMissing(): void
    {
        $default = 'default_value';
        $result = $this->adapter->get('nonexistent', $default);
        $this->assertEquals($default, $result);
    }

    public function testHasDetectsExistingKeys(): void
    {
        $this->adapter->set('exists', 'value');
        $this->assertTrue($this->adapter->has('exists'));
        $this->assertFalse($this->adapter->has('not_exists'));
    }

    public function testDeleteRemovesKey(): void
    {
        $this->adapter->set('to_delete', 'value');
        $this->assertTrue($this->adapter->delete('to_delete'));
        $this->assertFalse($this->adapter->has('to_delete'));
    }

    public function testSetMultiple(): void
    {
        $values = ['key1' => 'val1', 'key2' => 'val2', 'key3' => 'val3'];
        $this->assertTrue($this->adapter->setMultiple($values));

        foreach ($values as $key => $value) {
            $this->assertEquals($value, $this->adapter->get($key));
        }
    }

    public function testGetMultiple(): void
    {
        $this->adapter->set('a', '1');
        $this->adapter->set('b', '2');

        $result = $this->adapter->getMultiple(['a', 'b', 'nonexistent'], 'default');
        $this->assertEquals(['a' => '1', 'b' => '2', 'nonexistent' => 'default'], $result);
    }

    public function testTtlExpiration(): void
    {
        $this->adapter->set('short_lived', 'value', 1);
        $this->assertTrue($this->adapter->has('short_lived'));

        sleep(2);

        $this->assertFalse($this->adapter->has('short_lived'));
    }

    public function testClear(): void
    {
        $this->adapter->set('key1', 'val1');
        $this->adapter->set('key2', 'val2');

        $this->assertTrue($this->adapter->clear());
        $this->assertFalse($this->adapter->has('key1'));
        $this->assertFalse($this->adapter->has('key2'));
    }
}
```

## Writing Database Driver Tests

Database drivers require connection and query testing:

```php
declare(strict_types=1);
namespace Tests\Database\Drivers\Pdo;

use PHPUnit\Framework\TestCase;
use Spin\Database\Drivers\Pdo\MySqlDriver;

class MySqlDriverTest extends TestCase
{
    private MySqlDriver $driver;

    protected function setUp(): void
    {
        $this->driver = new MySqlDriver([
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => (int)(getenv('DB_PORT') ?: 3306),
            'database' => getenv('DB_NAME') ?: 'spin_test',
            'user' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: ''
        ]);

        $this->driver->connect();

        // Create test table
        $this->driver->query('DROP TABLE IF EXISTS test_users');
        $this->driver->query('
            CREATE TABLE test_users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255),
                email VARCHAR(255)
            )
        ');
    }

    protected function tearDown(): void
    {
        $this->driver->query('DROP TABLE IF EXISTS test_users');
        $this->driver->disconnect();
    }

    public function testInsertAndSelect(): void
    {
        $this->driver->query(
            'INSERT INTO test_users (name, email) VALUES (?, ?)',
            ['John Doe', 'john@example.com']
        );

        $result = $this->driver->query('SELECT * FROM test_users WHERE name = ?', ['John Doe']);
        $this->assertEquals('john@example.com', $result[0]['email']);
    }

    public function testUpdate(): void
    {
        $this->driver->query(
            'INSERT INTO test_users (name, email) VALUES (?, ?)',
            ['John', 'john@example.com']
        );

        $this->driver->query(
            'UPDATE test_users SET email = ? WHERE name = ?',
            ['newemail@example.com', 'John']
        );

        $result = $this->driver->query('SELECT * FROM test_users WHERE name = ?', ['John']);
        $this->assertEquals('newemail@example.com', $result[0]['email']);
    }

    public function testConnectionString(): void
    {
        $this->assertIsObject($this->driver->connection());
        $this->assertInstanceOf(\PDO::class, $this->driver->connection());
    }
}
```

## Integration Tests

Integration tests verify multiple components working together:

```php
declare(strict_types=1);
namespace Tests;

use PHPUnit\Framework\TestCase;
use Spin\Application;
use GuzzleHttp\Psr7\Request;

class ApplicationIntegrationTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application([
            'app' => ['name' => 'TestApp'],
            'cache' => ['adapter' => 'file'],
            'routes' => []
        ]);
    }

    public function testRequestResponseLifecycle(): void
    {
        $request = new Request('GET', '/api/users');
        $response = $this->app->handleRequest($request);

        $this->assertIsObject($response);
        $this->assertGreaterThanOrEqual(200, $response->getStatusCode());
        $this->assertLessThan(599, $response->getStatusCode());
    }

    public function testMiddlewarePipelineExecution(): void
    {
        // Middleware should execute in correct order
        $executed = [];

        $middleware1 = $this->createMock(Middleware::class);
        $middleware1->method('handle')->willReturnCallback(function () use (&$executed) {
            $executed[] = 'middleware1';
            return true;
        });

        $middleware2 = $this->createMock(Middleware::class);
        $middleware2->method('handle')->willReturnCallback(function () use (&$executed) {
            $executed[] = 'middleware2';
            return true;
        });

        // Add middleware and verify execution order
        // ... test implementation
        $this->assertEquals(['middleware1', 'middleware2'], $executed);
    }
}
```

## Test Data and Fixtures

Use fixtures for consistent test data:

```php
// tests/Fixtures/UserFixture.php
declare(strict_types=1);
namespace Tests\Fixtures;

class UserFixture
{
    public static function validUser(): array
    {
        return [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'created_at' => '2026-01-01 00:00:00'
        ];
    }

    public static function adminUser(): array
    {
        return array_merge(self::validUser(), ['role' => 'admin']);
    }

    public static function invalidUser(): array
    {
        return [
            'id' => null,
            'name' => '',
            'email' => 'invalid-email'
        ];
    }
}
```

Use in tests:

```php
public function testUserValidation(): void
{
    $user = UserFixture::validUser();
    $this->assertTrue($this->validator->validate($user));
}
```

## Coverage Requirements

### Minimum Standards

- **Core components** (Application, Controller, Middleware, Config): 90%+
- **Cache adapters**: 85%+
- **Database drivers**: 80%+
- **Helpers and utilities**: 80%+
- **New features**: 85%+

### What to Test

- ✓ Happy path (normal operation)
- ✓ Edge cases (empty inputs, boundaries)
- ✓ Error conditions (exceptions, validation failures)
- ✓ State changes (data persistence, side effects)
- ✓ Integration points (services interacting)

### What NOT to Test

- ✗ Third-party library behavior (trust it works)
- ✗ PHP standard library behavior
- ✗ Simple getters/setters without logic
- ✗ Private implementation details

## Running Tests Locally

```bash
# Run all tests
./vendor/bin/phpunit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/

# Run specific test file
./vendor/bin/phpunit tests/Core/ConfigTest.php

# Run tests matching pattern
./vendor/bin/phpunit --filter testGetUser

# Verbose output
./vendor/bin/phpunit -v

# Stop on first failure
./vendor/bin/phpunit --stop-on-failure

# Check code coverage
./vendor/bin/phpunit --coverage-text
```

## CI/CD Pipeline Integration

Tests run automatically via GitHub Actions:

1. **On every push** to branches
2. **On pull requests** to develop/master
3. **Required before merge** to master (checks coverage, failures)

Verify locally before pushing:

```bash
./vendor/bin/phpunit && ./vendor/bin/phpunit --coverage-text
```

## Debugging Tests

```php
// Print debug output
echo "Debug: " . json_encode($variable);
// Or use var_dump()
var_dump($variable);

// Stop execution at breakpoint
$this->markTestIncomplete('Debug here');

// Use PHPUnit's built-in debugging
$this->markTestSkipped('Temporarily disabled');
```

---

**Last Updated:** 2026-03-15
**Framework Version:** 0.0.36+
