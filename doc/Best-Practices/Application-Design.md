# Application Design

## Overview

Building maintainable SPIN applications requires thoughtful separation of concerns and a clear architectural structure. This guide explores layered architecture patterns, dependency injection practices, and organization strategies that scale from small apps to complex systems.

## Separation of Concerns

The core principle is isolating responsibilities into distinct layers. SPIN's lightweight nature means you control the architecture—there's no enforced convention, which makes discipline essential.

### Three-Layer Model

**Controllers** — HTTP entry points only
- Validate input (use middleware or controller-level guards)
- Delegate to services
- Format responses
- Never contain business logic

```php
public function handlePOST(array $args): ResponseInterface
{
    $data = getRequest()->getParsedBody();

    // Validation
    if (empty($data['email'])) {
        return responseJson(['error' => 'Email required'], 400);
    }

    // Delegate
    $user = $this->userService->create($data);

    // Format and return
    return responseJson(['id' => $user->getId()], 201);
}
```

**Services** — business logic and orchestration
- Implement domain rules
- Coordinate repositories and external APIs
- Are testable in isolation
- Consumed by controllers and other services

```php
class UserService
{
    public function __construct(private UserRepository $users) {}

    public function create(array $data): User
    {
        // Validate domain constraints
        if ($this->users->existsByEmail($data['email'])) {
            throw new UserAlreadyExistsException();
        }

        $user = new User($data['email'], hash('bcrypt', $data['password']));
        return $this->users->save($user);
    }
}
```

**Repositories** — data access abstraction
- Encapsulate database queries
- Hide storage implementation (allows swapping MySQL for PostgreSQL)
- Return domain objects, never raw data
- Implement collection-like interfaces (find, findAll, save, delete)

```php
class UserRepository
{
    public function __construct(private Connection $db) {}

    public function find(int $id): ?User
    {
        $row = $this->db->fetch('SELECT * FROM users WHERE id = ?', [$id]);
        return $row ? User::fromRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
        return $row ? User::fromRow($row) : null;
    }
}
```

## Dependency Injection Patterns

SPIN's container (PSR-11) is available via `app()->getContainer()`. Constructor injection is the standard pattern.

### Automatic Registration

Define services in `config-{env}.json` under a `services` section, or register them in your bootstrap code:

```php
$container = app()->getContainer();
$container->add(UserRepository::class, function (ContainerInterface $c) {
    return new UserRepository($c->get(Connection::class));
});
$container->add(UserService::class, function (ContainerInterface $c) {
    return new UserService($c->get(UserRepository::class));
});
```

### Factory Methods vs. Classes

Use factory closures for simple cases; create factory classes for complex initialization:

```php
// Simple: closure
$container->add(Cache::class, fn() => cache('redis'));

// Complex: factory class
$container->add(PaymentGateway::class, PaymentGatewayFactory::class);

class PaymentGatewayFactory
{
    public function __invoke(ContainerInterface $c): PaymentGateway
    {
        $config = config('payment');
        return new PaymentGateway($config['apiKey'], $config['endpoint']);
    }
}
```

## Organization Strategies

### By Feature (Recommended for Most Apps)

Organize directories around business domains:

```
src/
  Users/
    User.php
    UserController.php
    UserService.php
    UserRepository.php
  Products/
    Product.php
    ProductController.php
    ProductService.php
    ProductRepository.php
    ProductImageService.php
  Orders/
    Order.php
    OrderController.php
    OrderService.php
    OrderRepository.php
```

**Advantages:**
- Related code lives together
- Easy to understand a feature in isolation
- Scales well as the app grows
- Reduces context switching

### By Layer (Use for Larger Teams)

Group by architectural layer:

```
src/
  Controllers/
    UserController.php
    ProductController.php
  Services/
    UserService.php
    ProductService.php
  Repositories/
    UserRepository.php
    ProductRepository.php
  Models/
    User.php
    Product.php
```

**Advantages:**
- Clear separation of concerns
- Easier for new team members to find patterns
- Useful when teams own specific layers

**Disadvantages:**
- More file navigation
- Harder to modify a complete feature
- Scaling introduces subdirectories anyway

## Service Classes: When and Why

Use services to encapsulate reusable business logic that multiple controllers might need:

```php
// Reused across User registration, password reset, etc.
class EmailService
{
    public function sendWelcome(User $user): bool { ... }
    public function sendResetToken(User $user, string $token): bool { ... }
}

// Controllers both use it
class UserRegistrationController
{
    public function __construct(private EmailService $email) {}

    public function handlePOST(array $args): ResponseInterface
    {
        $user = ...; // create user
        $this->email->sendWelcome($user);
        return responseJson(['id' => $user->getId()], 201);
    }
}
```

## Repositories: Query Encapsulation

Never leak SQL into controllers. Repositories hide storage details and make unit testing easier:

```php
// Good: repository method
$user = $userRepo->findByEmailWithProfile($email);

// Bad: controller with query logic
$user = $db->fetch(
    'SELECT u.*, p.* FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.email = ?',
    [$email]
);
```

## Key Takeaways

1. **Controllers validate and delegate** — no business logic
2. **Services implement rules** — independent of HTTP
3. **Repositories abstract storage** — swap implementations without changing services
4. **Dependency injection wires everything** — use the container
5. **Choose organization by feature for most projects** — layer-by-layer for large teams
6. **Keep layers thin** — a 500-line service signals it's doing too much

---

**See also:** [Testing-Patterns.md](Testing-Patterns.md), [Error-Handling.md](Error-Handling.md)
