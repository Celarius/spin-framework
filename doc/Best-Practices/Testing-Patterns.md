# Testing Patterns

## Overview

Testing ensures code correctness, enables refactoring confidence, and documents expected behavior. This guide covers test organization, unit vs. integration testing, mocking strategies, and coverage practices for SPIN applications.

## Unit Tests vs. Integration Tests

### Unit Tests

Test a single class in isolation with mocked dependencies:

```php
// tests/Services/UserServiceTest.php
class UserServiceTest extends TestCase
{
    private UserService $service;
    private MockObject $repository;
    private MockObject $emailService;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->emailService = $this->createMock(EmailService::class);
        $this->service = new UserService($this->repository, $this->emailService);
    }

    public function testCreateUserSucceeds(): void
    {
        // Arrange
        $data = ['email' => 'user@example.com', 'password' => 'secret123'];
        $user = new User(1, 'user@example.com', 'hashed_password');

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('user@example.com')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn($user);

        $this->emailService
            ->expects($this->once())
            ->method('sendWelcome')
            ->with($user);

        // Act
        $result = $this->service->create($data);

        // Assert
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('user@example.com', $result->getEmail());
    }

    public function testCreateUserFailsIfEmailExists(): void
    {
        // Arrange
        $data = ['email' => 'user@example.com', 'password' => 'secret123'];
        $existingUser = new User(1, 'user@example.com', 'hashed_password');

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($existingUser);

        // Act & Assert
        $this->expectException(UserAlreadyExistsException::class);
        $this->service->create($data);
    }
}
```

**Advantages:**
- Fast (no I/O, no network)
- Focused (test one thing)
- Isolate bugs to specific unit

**Disadvantages:**
- Heavy mocking can hide integration bugs
- Mocks may not reflect real behavior

### Integration Tests

Test multiple components working together:

```php
// tests/Controllers/UserControllerTest.php
class UserControllerTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        // Real database connection, real services
        $this->app = createTestApplication();
        // Seed test data
        $this->seedTestDatabase();
    }

    public function testCreateUserEndpoint(): void
    {
        // Arrange
        $request = $this->createPostRequest('/users', [
            'email' => 'newuser@example.com',
            'password' => 'SecurePassword123'
        ]);

        // Act
        $response = $this->app->handle($request);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('id', $body);

        // Verify user in database
        $user = $this->app->getContainer()
            ->get(UserRepository::class)
            ->find($body['id']);
        $this->assertNotNull($user);
        $this->assertEquals('newuser@example.com', $user->getEmail());
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        // Arrange
        $request = $this->createPostRequest('/users', [
            'email' => 'not-an-email',
            'password' => 'SecurePassword123'
        ]);

        // Act
        $response = $this->app->handle($request);

        // Assert
        $this->assertEquals(400, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('fields', $body);
        $this->assertArrayHasKey('email', $body['fields']);
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanupTestDatabase();
    }
}
```

**Advantages:**
- Detect real-world bugs
- Verify components interact correctly
- Test actual business flows

**Disadvantages:**
- Slower (I/O, database)
- Harder to debug (many moving parts)
- Setup/teardown complexity

## Test Organization

Mirror `src/` structure in `tests/`:

```
src/
  Services/
    UserService.php
    ProductService.php
  Controllers/
    UserController.php
  Repositories/
    UserRepository.php

tests/
  Services/
    UserServiceTest.php
    ProductServiceTest.php
  Controllers/
    UserControllerTest.php
  Repositories/
    UserRepositoryTest.php
```

Use namespaces to match:

```php
// src/Services/UserService.php
namespace App\Services;

// tests/Services/UserServiceTest.php
namespace App\Services\Tests;
```

## Mocking and Test Doubles

### Mock Objects (Verify Behavior)

Use mocks to verify interactions:

```php
public function testSendWelcomeEmail(): void
{
    $emailService = $this->createMock(EmailService::class);

    // Expect sendWelcome to be called once with a User
    $emailService
        ->expects($this->once())
        ->method('sendWelcome')
        ->with($this->isInstanceOf(User::class));

    $service = new UserService($emailService);
    $user = new User(1, 'user@example.com', 'hashed');
    $service->notifyNewUser($user);
}
```

### Stub Objects (Return Values)

Use stubs to provide test data:

```php
public function testFindUserReturnsData(): void
{
    $repository = $this->createMock(UserRepository::class);
    $user = new User(1, 'user@example.com', 'hashed');

    // Stub: always return this user
    $repository
        ->method('find')
        ->willReturn($user);

    $service = new UserService($repository);
    $result = $service->getUser(1);

    $this->assertEquals($user, $result);
}
```

### Partial Mocks (Spy on Real Objects)

Test real methods while mocking others:

```php
public function testLogsDatabaseFailure(): void
{
    $repository = $this->createPartialMock(
        UserRepository::class,
        ['getDatabase']
    );

    // Mock the database, keep real methods
    $mockDb = $this->createMock(Connection::class);
    $mockDb->method('execute')->willThrowException(
        new \PDOException('Connection failed')
    );

    $repository->method('getDatabase')->willReturn($mockDb);

    $this->expectException(\PDOException::class);
    $repository->save($user);
}
```

## Fixtures and Data Setup

### Factory Patterns

Create realistic test data:

```php
// tests/Factories/UserFactory.php
class UserFactory
{
    public static function create(array $override = []): User
    {
        return new User([
            'id' => 1,
            'email' => 'user@example.com',
            'password_hash' => password_hash('password', PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s'),
            ...$override
        ]);
    }

    public static function createMany(int $count, array $override = []): array
    {
        return array_map(
            fn($i) => self::create(['id' => $i, ...$override]),
            range(1, $count)
        );
    }
}

// Usage in tests
$user = UserFactory::create(['email' => 'admin@example.com']);
$users = UserFactory::createMany(10);
```

### Database Fixtures

Pre-populate test database:

```php
// tests/Fixtures/users.sql
INSERT INTO users (id, email, password_hash, created_at) VALUES
(1, 'alice@example.com', '$2y$10$...', '2024-01-01 00:00:00'),
(2, 'bob@example.com', '$2y$10$...', '2024-01-02 00:00:00');

// Usage in test
protected function setUp(): void
{
    $this->loadFixture('users.sql');
}
```

Or use factories in code:

```php
protected function setUp(): void
{
    $this->db->execute('DELETE FROM users');
    $factory = new UserFactory($this->db);
    $factory->create(['email' => 'test@example.com']);
}
```

## Test Scenarios and Assertions

### Happy Path

Test the normal, successful flow:

```php
public function testSuccessfulLogin(): void
{
    // Valid credentials
    $result = $this->service->login('user@example.com', 'correct_password');

    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['token']);
    $this->assertEquals(200, $result['status']);
}
```

### Error Cases

Test failures and edge cases:

```php
public function testLoginWithInvalidPassword(): void
{
    $this->expectException(AuthenticationException::class);
    $this->service->login('user@example.com', 'wrong_password');
}

public function testLoginWithNonexistentUser(): void
{
    $this->expectException(UserNotFoundException::class);
    $this->service->login('nonexistent@example.com', 'password');
}

public function testLoginWithEmptyEmail(): void
{
    $this->expectException(ValidationException::class);
    $this->service->login('', 'password');
}
```

### Boundary Conditions

Test limits and edge cases:

```php
public function testCreateUserWithMaxEmailLength(): void
{
    $longEmail = str_repeat('a', 243) . '@example.com'; // 255 chars
    $user = $this->service->create(['email' => $longEmail]);
    $this->assertNotNull($user->getId());
}

public function testCreateUserWithTooLongEmail(): void
{
    $tooLongEmail = str_repeat('a', 244) . '@example.com'; // 256 chars
    $this->expectException(ValidationException::class);
    $this->service->create(['email' => $tooLongEmail]);
}
```

## Coverage Targets and Metrics

Aim for meaningful coverage, not perfect coverage:

```bash
# Run with coverage report
./vendor/bin/phpunit --coverage-html coverage/

# Target: 80%+ for business logic, 50%+ for utilities
```

### What to Prioritize

1. **Business logic** (services, repositories) — 80%+
2. **Controllers** (request handling) — 70%+
3. **Models/Entities** (getters/setters) — 50%+
4. **Utilities** (helpers, formatters) — 50%+

### What NOT to Obsess Over

- **Auto-generated code** — constructors, getters
- **Framework code** — SPIN's request/response pipeline
- **Third-party libraries** — trust their tests
- **Configuration** — static data

## Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test class
./vendor/bin/phpunit tests/Services/UserServiceTest.php

# Run specific test method
./vendor/bin/phpunit tests/Services/UserServiceTest.php --filter testCreateUserSucceeds

# Stop on first failure
./vendor/bin/phpunit --stop-on-failure

# Watch mode (requires PHPUnit watcher)
phpunit-watcher watch
```

## Key Takeaways

1. **Mix unit and integration tests** — fast unit tests for logic, integration tests for workflows
2. **Mirror src/ structure in tests/** — easy to find corresponding test
3. **Mock external dependencies** — keep unit tests focused
4. **Use factories for test data** — DRY, readable, maintainable
5. **Test happy path, errors, and boundaries** — comprehensive coverage
6. **Aim for 80%+ on business logic** — meaningful coverage, not perfectionism
7. **Keep tests maintainable** — complex tests break when code changes
8. **Test behavior, not implementation** — refactoring shouldn't break tests

---

**See also:** [Application-Design.md](Application-Design.md), [Error-Handling.md](Error-Handling.md), [User-Guide/Testing.md](../User-Guide/Testing.md)
