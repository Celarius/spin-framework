# Testing

SPIN Framework provides comprehensive testing support to ensure your application works correctly and reliably. This guide covers unit testing, integration testing, and testing best practices.

## Testing Overview

### Testing Types

- **Unit Tests** - Test individual components in isolation
- **Integration Tests** - Test how components work together
- **Feature Tests** - Test complete features end-to-end
- **Performance Tests** - Test application performance and scalability

### Testing Tools

- **PHPUnit** - Primary testing framework
- **Mockery** - Mocking and stubbing library
- **Faker** - Data generation for tests
- **Code Coverage** - Test coverage analysis

## Setting Up Testing

### Installation

```bash
# Install testing dependencies
composer require --dev phpunit/phpunit
composer require --dev mockery/mockery
composer require --dev fakerphp/faker
```

### PHPUnit Configuration

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         processIsolation="false"
         stopOnFailure="false"
         cacheDirectory=".phpunit.cache">
    
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
        <exclude>
            <directory>vendor</directory>
            <directory>tests</directory>
        </exclude>
        <report>
            <html outputDirectory="coverage"/>
            <clover outputFile="coverage.xml"/>
        </report>
    </coverage>
    
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

### Test Bootstrap

```php
<?php
// tests/bootstrap.php

require_once __DIR__ . '/../vendor/autoload.php';

// Set testing environment
putenv('APP_ENV=testing');

// Load test configuration
$config = require __DIR__ . '/../app/Config/app-test.php';

// Initialize test database
if (isset($config['database'])) {
    // Set up test database connection
}
```

## Unit Testing

### Basic Unit Test

```php
<?php
// tests/Unit/Helpers/ArrayHelperTest.php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    public function testArrayFlatten()
    {
        $input = [1, [2, 3], [4, [5, 6]]];
        $expected = [1, 2, 3, 4, 5, 6];
        
        $result = ArrayHelper::flatten($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testArrayFlattenWithEmptyArray()
    {
        $input = [];
        $expected = [];
        
        $result = ArrayHelper::flatten($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testArrayFlattenWithNull()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Input must be an array');
        
        ArrayHelper::flatten(null);
    }
}
```

### Testing Controllers

```php
<?php
// tests/Unit/Controllers/UserControllerTest.php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use App\Services\UserService;
use Mockery;

class UserControllerTest extends TestCase
{
    private UserController $controller;
    private UserService $userService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userService = Mockery::mock(UserService::class);
        $this->controller = new UserController($this->userService);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function testIndexReturnsUsers()
    {
        $expectedUsers = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith']
        ];
        
        $this->userService
            ->shouldReceive('getAllUsers')
            ->once()
            ->andReturn($expectedUsers);
        
        $result = $this->controller->index();
        
        $this->assertEquals($expectedUsers, $result);
    }
    
    public function testShowReturnsUser()
    {
        $userId = 1;
        $expectedUser = ['id' => 1, 'name' => 'John Doe'];
        
        $this->userService
            ->shouldReceive('getUserById')
            ->with($userId)
            ->once()
            ->andReturn($expectedUser);
        
        $result = $this->controller->show(['id' => $userId]);
        
        $this->assertEquals($expectedUser, $result);
    }
    
    public function testShowThrowsExceptionForInvalidId()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->controller->show(['id' => 'invalid']);
    }
}
```

### Testing Services

```php
<?php
// tests/Unit/Services/UserServiceTest.php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;
use App\Repositories\UserRepository;
use App\Models\User;
use Mockery;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->userService = new UserService($this->userRepository);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function testCreateUser()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $expectedUser = new User($userData);
        
        $this->userRepository
            ->shouldReceive('create')
            ->with($userData)
            ->once()
            ->andReturn($expectedUser);
        
        $result = $this->userService->createUser($userData);
        
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($userData['name'], $result->name);
        $this->assertEquals($userData['email'], $result->email);
    }
    
    public function testCreateUserWithInvalidData()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ];
        
        $this->expectException(\InvalidArgumentException::class);
        
        $this->userService->createUser($userData);
    }
}
```

## Integration Testing

### Testing Database Operations

```php
<?php
// tests/Integration/UserRepositoryTest.php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Repositories\UserRepository;
use App\Models\User;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
    private \PDO $pdo;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test database
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Create test tables
        $this->createTables();
        
        $this->userRepository = new UserRepository($this->pdo);
    }
    
    private function createTables(): void
    {
        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    public function testCreateAndFindUser()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT)
        ];
        
        $user = $this->userRepository->create($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        
        // Find the user
        $foundUser = $this->userRepository->findById($user->id);
        
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }
    
    public function testUpdateUser()
    {
        // Create user first
        $user = $this->userRepository->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        // Update user
        $updatedData = ['name' => 'John Smith'];
        $updatedUser = $this->userRepository->update($user->id, $updatedData);
        
        $this->assertEquals($updatedData['name'], $updatedUser->name);
        
        // Verify in database
        $foundUser = $this->userRepository->findById($user->id);
        $this->assertEquals($updatedData['name'], $foundUser->name);
    }
    
    public function testDeleteUser()
    {
        // Create user first
        $user = $this->userRepository->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        // Delete user
        $this->userRepository->delete($user->id);
        
        // Verify user is deleted
        $foundUser = $this->userRepository->findById($user->id);
        $this->assertNull($foundUser);
    }
}
```

### Testing API Endpoints

```php
<?php
// tests/Integration/Api/UserApiTest.php

namespace Tests\Integration\Api;

use PHPUnit\Framework\TestCase;
use App\Application;

class UserApiTest extends TestCase
{
    private Application $app;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app = new Application();
        $this->app->bootstrap();
    }
    
    public function testGetUsersEndpoint()
    {
        $request = $this->createRequest('GET', '/api/users');
        $response = $this->app->handle($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($data);
    }
    
    public function testCreateUserEndpoint()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $request = $this->createRequest('POST', '/api/users', $userData);
        $response = $this->app->handle($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($userData['name'], $data['name']);
        $this->assertEquals($userData['email'], $data['email']);
    }
    
    public function testCreateUserWithInvalidData()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email'
        ];
        
        $request = $this->createRequest('POST', '/api/users', $userData);
        $response = $this->app->handle($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('errors', $data);
    }
    
    private function createRequest(string $method, string $uri, array $data = []): \Psr\Http\Message\RequestInterface
    {
        $request = new \GuzzleHttp\Psr7\Request($method, $uri);
        
        if (!empty($data)) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $request = $request->withBody(\GuzzleHttp\Psr7\Utils::streamFor(json_encode($data)));
        }
        
        return $request;
    }
}
```

## Feature Testing

### Testing Complete Features

```php
<?php
// tests/Feature/UserRegistrationTest.php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Application;

class UserRegistrationTest extends TestCase
{
    private Application $app;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app = new Application();
        $this->app->bootstrap();
    }
    
    public function testUserCanRegister()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        
        // Submit registration form
        $request = $this->createRequest('POST', '/register', $userData);
        $response = $this->app->handle($request);
        
        $this->assertEquals(302, $response->getStatusCode()); // Redirect after success
        
        // Verify user was created
        $user = $this->getUserByEmail($userData['email']);
        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user->name);
        
        // Verify user can login
        $loginResponse = $this->attemptLogin($userData['email'], $userData['password']);
        $this->assertEquals(302, $loginResponse->getStatusCode());
    }
    
    public function testUserCannotRegisterWithInvalidData()
    {
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456'
        ];
        
        $request = $this->createRequest('POST', '/register', $invalidData);
        $response = $this->app->handle($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('name', $data['errors']);
        $this->assertArrayHasKey('email', $data['errors']);
        $this->assertArrayHasKey('password', $data['errors']);
    }
    
    public function testUserCannotRegisterWithExistingEmail()
    {
        // Create first user
        $this->createUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
        
        // Try to register with same email
        $userData = [
            'name' => 'Jane Smith',
            'email' => 'john@example.com',
            'password' => 'password456',
            'password_confirmation' => 'password456'
        ];
        
        $request = $this->createRequest('POST', '/register', $userData);
        $response = $this->app->handle($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('email', $data['errors']);
    }
    
    private function createRequest(string $method, string $uri, array $data = []): \Psr\Http\Message\RequestInterface
    {
        $request = new \GuzzleHttp\Psr7\Request($method, $uri);
        
        if (!empty($data)) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $request = $request->withBody(\GuzzleHttp\Psr7\Utils::streamFor(json_encode($data)));
        }
        
        return $request;
    }
    
    private function getUserByEmail(string $email)
    {
        // Implementation to get user from database
        return null;
    }
    
    private function createUser(array $data)
    {
        // Implementation to create user in database
    }
    
    private function attemptLogin(string $email, string $password): \Psr\Http\Message\ResponseInterface
    {
        $loginData = ['email' => $email, 'password' => $password];
        $request = $this->createRequest('POST', '/login', $loginData);
        return $this->app->handle($request);
    }
}
```

## Testing Middleware

### Testing Custom Middleware

```php
<?php
// tests/Unit/Middleware/AuthMiddlewareTest.php

namespace Tests\Unit\Middleware;

use PHPUnit\Framework\TestCase;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Mockery;

class AuthMiddlewareTest extends TestCase
{
    private AuthMiddleware $middleware;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AuthMiddleware();
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function testMiddlewareAllowsAuthenticatedRequest()
    {
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        
        $request->shouldReceive('getHeaderLine')
            ->with('Authorization')
            ->andReturn('Bearer valid-token');
        
        $next = function ($req, $res) {
            return $res;
        };
        
        $result = $this->middleware->process($request, $response, $next);
        
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    
    public function testMiddlewareRejectsUnauthenticatedRequest()
    {
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        
        $request->shouldReceive('getHeaderLine')
            ->with('Authorization')
            ->andReturn('');
        
        $next = function ($req, $res) {
            return $res;
        };
        
        $result = $this->middleware->process($request, $response, $next);
        
        $this->assertEquals(401, $result->getStatusCode());
    }
    
    public function testMiddlewareRejectsInvalidToken()
    {
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        
        $request->shouldReceive('getHeaderLine')
            ->with('Authorization')
            ->andReturn('Bearer invalid-token');
        
        $next = function ($req, $res) {
            return $res;
        };
        
        $result = $this->middleware->process($request, $response, $next);
        
        $this->assertEquals(401, $result->getStatusCode());
    }
}
```

## Testing Utilities

### Test Data Factories

```php
<?php
// tests/Factories/UserFactory.php

namespace Tests\Factories;

use App\Models\User;
use Faker\Factory as Faker;

class UserFactory
{
    private static $faker;
    
    public static function make(array $attributes = []): User
    {
        if (!self::$faker) {
            self::$faker = Faker::create();
        }
        
        $defaults = [
            'name' => self::$faker->name,
            'email' => self::$faker->unique()->safeEmail,
            'password' => 'password123'
        ];
        
        $data = array_merge($defaults, $attributes);
        
        return new User($data);
    }
    
    public static function create(array $attributes = []): User
    {
        $user = self::make($attributes);
        
        // Save to database
        $user->save();
        
        return $user;
    }
    
    public static function createMany(int $count, array $attributes = []): array
    {
        $users = [];
        
        for ($i = 0; $i < $count; $i++) {
            $users[] = self::create($attributes);
        }
        
        return $users;
    }
}
```

### Using Test Factories

```php
<?php
// tests/Unit/Services/UserServiceTest.php

use Tests\Factories\UserFactory;

class UserServiceTest extends TestCase
{
    public function testGetAllUsers()
    {
        // Create test users
        $users = UserFactory::createMany(3);
        
        $result = $this->userService->getAllUsers();
        
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(User::class, $result);
    }
    
    public function testGetUserById()
    {
        $user = UserFactory::create(['name' => 'John Doe']);
        
        $result = $this->userService->getUserById($user->id);
        
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->name);
    }
}
```

## Performance Testing

### Basic Performance Tests

```php
<?php
// tests/Performance/UserServicePerformanceTest.php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;

class UserServicePerformanceTest extends TestCase
{
    private UserService $userService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }
    
    public function testGetAllUsersPerformance()
    {
        $startTime = microtime(true);
        
        $users = $this->userService->getAllUsers();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $this->assertLessThan(100, $executionTime, 'Query took longer than 100ms');
        $this->assertNotEmpty($users);
    }
    
    public function testCreateUserPerformance()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $startTime = microtime(true);
        
        $user = $this->userService->createUser($userData);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $this->assertLessThan(50, $executionTime, 'User creation took longer than 50ms');
        $this->assertInstanceOf(User::class, $user);
    }
}
```

## Code Coverage

### Running Tests with Coverage

```bash
# Generate HTML coverage report
./vendor/bin/phpunit --coverage-html coverage

# Generate XML coverage report
./vendor/bin/phpunit --coverage-clover coverage.xml

# Generate text coverage report
./vendor/bin/phpunit --coverage-text
```

### Coverage Configuration

```xml
<coverage>
    <include>
        <directory suffix=".php">src</directory>
        <directory suffix=".php">app</directory>
    </include>
    <exclude>
        <directory>vendor</directory>
        <directory>tests</directory>
        <directory>storage</directory>
    </exclude>
    <report>
        <html outputDirectory="coverage"/>
        <clover outputFile="coverage.xml"/>
        <text outputFile="coverage.txt"/>
    </report>
</coverage>
```

## Testing Best Practices

### 1. Test Organization

- Group related tests in test suites
- Use descriptive test method names
- Follow AAA pattern (Arrange, Act, Assert)
- Keep tests focused and simple

### 2. Test Data Management

- Use factories for test data generation
- Clean up test data after each test
- Use database transactions for test isolation
- Avoid hardcoded test data

### 3. Mocking and Stubbing

- Mock external dependencies
- Stub complex operations
- Verify mock interactions
- Use realistic test data

### 4. Test Coverage

- Aim for high test coverage (80%+)
- Focus on critical business logic
- Test edge cases and error conditions
- Don't test framework code

### 5. Performance Considerations

- Keep tests fast
- Use in-memory databases for testing
- Avoid unnecessary I/O operations
- Mock external services

## Running Tests

### Command Line

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit

# Run specific test file
./vendor/bin/phpunit tests/Unit/Controllers/UserControllerTest.php

# Run specific test method
./vendor/bin/phpunit --filter testCreateUser

# Run tests with verbose output
./vendor/bin/phpunit --verbose

# Run tests and stop on first failure
./vendor/bin/phpunit --stop-on-failure
```

### Continuous Integration

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_sqlite
        tools: composer:v2
        
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
        
    - name: Run tests
      run: ./vendor/bin/phpunit --coverage-clover coverage.xml
        
    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v1
      with:
        file: ./coverage.xml
```

## Test Examples

### Complete Test Class

```php
<?php
// tests/Unit/Services/EmailServiceTest.php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\EmailService;
use App\Services\TemplateService;
use App\Services\MailerService;
use Mockery;

class EmailServiceTest extends TestCase
{
    private EmailService $emailService;
    private TemplateService $templateService;
    private MailerService $mailerService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->templateService = Mockery::mock(TemplateService::class);
        $this->mailerService = Mockery::mock(MailerService::class);
        
        $this->emailService = new EmailService(
            $this->templateService,
            $this->mailerService
        );
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function testSendWelcomeEmail()
    {
        $user = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $expectedTemplate = '<h1>Welcome John Doe!</h1>';
        $expectedSubject = 'Welcome to Our Application';
        
        $this->templateService
            ->shouldReceive('render')
            ->with('emails.welcome', $user)
            ->once()
            ->andReturn($expectedTemplate);
        
        $this->mailerService
            ->shouldReceive('send')
            ->with($user['email'], $expectedSubject, $expectedTemplate)
            ->once()
            ->andReturn(true);
        
        $result = $this->emailService->sendWelcomeEmail($user);
        
        $this->assertTrue($result);
    }
    
    public function testSendWelcomeEmailWithInvalidUser()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User data is required');
        
        $this->emailService->sendWelcomeEmail([]);
    }
    
    public function testSendWelcomeEmailWithMissingEmail()
    {
        $user = ['name' => 'John Doe'];
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User email is required');
        
        $this->emailService->sendWelcomeEmail($user);
    }
    
    public function testSendWelcomeEmailWhenMailerFails()
    {
        $user = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $this->templateService
            ->shouldReceive('render')
            ->with('emails.welcome', $user)
            ->once()
            ->andReturn('<h1>Welcome John Doe!</h1>');
        
        $this->mailerService
            ->shouldReceive('send')
            ->once()
            ->andReturn(false);
        
        $result = $this->emailService->sendWelcomeEmail($user);
        
        $this->assertFalse($result);
    }
}
```

By following these testing practices, you can ensure your SPIN Framework application is reliable, maintainable, and bug-free. Comprehensive testing gives you confidence in your code and makes refactoring and updates much safer.
