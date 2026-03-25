# Code Standards — SPIN Framework

This document outlines the coding standards and quality requirements for SPIN Framework contributions.

## PSR-4 Namespace Mapping

All code must follow PSR-4 autoloading standard:

**Namespace → File Mapping:**

```
Spin\Core\Controller         → src/Core/Controller.php
Spin\Cache\Adapters\Redis    → src/Cache/Adapters/Redis.php
Spin\Database\Drivers\Pdo\MySQL → src/Database/Drivers/Pdo/MySQL.php
App\Controllers\UserController → src/Controllers/UserController.php (application)
```

**composer.json configuration:**

```json
{
  "autoload": {
    "psr-4": {
      "Spin\\": "src/",
      "Tests\\": "tests/"
    }
  }
}
```

**Non-negotiable:** Breaking PSR-4 mapping creates autoloading failures and must not be done.

## PSR-12 Code Style

All code must adhere to PSR-12 extended coding style. Use PHP-CS-Fixer for enforcement:

### Indentation and Spacing

```php
// Correct: 4 spaces, no tabs
public function exampleMethod(): void
{
    if ($condition) {
        $value = 'something';
    }
}

// Incorrect: 2 spaces or tabs
public function exampleMethod(): void
{
  if ($condition) {
    $value = 'something';
  }
}
```

### Line Length

- **Maximum 120 characters** per line
- Break long method chains and arrays
- Break long conditional expressions

```php
// Correct
$result = $this->service
    ->getData()
    ->filter(static fn($item) => $item->isActive())
    ->map(static fn($item) => $item->toArray());

// Incorrect
$result = $this->service->getData()->filter(static fn($item) => $item->isActive())->map(static fn($item) => $item->toArray());
```

### Braces and Control Structures

```php
// Correct: Opening brace on same line
if ($condition) {
    doSomething();
} else {
    doOtherThing();
}

// Correct: Braces required even for single statements
if ($condition) {
    return true;
}

// Incorrect: No space before condition
if($condition) { }

// Incorrect: Opening brace on new line
if ($condition)
{
    doSomething();
}
```

### Method Declarations

```php
// Correct
public function methodName(string $param1, int $param2): string
{
    return $param1;
}

// Correct: Multiple parameters on separate lines
public function complexMethod(
    string $firstName,
    string $lastName,
    int $age,
    array $metadata = []
): array {
    return [];
}

// Incorrect: Space between name and parentheses
public function methodName ($param) { }

// Incorrect: Missing return type
public function methodName(string $param)
{
}
```

### Spacing Rules

```php
// Correct: Space after keywords
if ($condition) { }
foreach ($items as $item) { }
switch ($value) { }

// Correct: Space around operators
$result = $a + $b;
$value = $a === $b;

// Correct: Space after commas in arguments
function call($arg1, $arg2, $arg3);

// Incorrect: No space after keyword
if($condition) { }
foreach($items as $item) { }

// Incorrect: Space around assignment operators
$result = $a +$b;
```

## Strict Types Declaration

**Every file must start with strict types:**

```php
declare(strict_types=1);

namespace Spin\Core;

class MyClass { }
```

**Why:** Prevents type coercion bugs and ensures type safety throughout the framework.

## Type Hints

### Parameter Types

**Always declare parameter types:**

```php
// Correct
public function process(string $name, int $id, array $options = []): void
{
    // implementation
}

// Incorrect: Missing types
public function process($name, $id, $options = [])
{
}

// Incorrect: Using mixed
public function process(mixed $value): void
{
    // Hard to test, poor IDE support
}
```

### Return Types

**Always declare return types:**

```php
// Correct: Void
public function configure(array $config): void
{
    // no return
}

// Correct: Specific type
public function getName(): string
{
    return $this->name;
}

// Correct: Nullable type
public function getOptional(): ?string
{
    return $this->optional ?? null;
}

// Correct: Union types (PHP 8.0+)
public function getValue(): string|int|null
{
    return $this->value;
}

// Incorrect: Missing return type
public function getValue()
{
    return $this->value;
}
```

### Union and Intersection Types

```php
// Correct: Union types (PHP 8.0+)
public function handle(CacheInterface|DatabaseInterface $service): void
{
}

// Correct: Intersection types (PHP 8.1+)
public function process(Countable&ArrayAccess $collection): void
{
}

// Avoid: Generic type aliases that don't exist
public function getData(): array|object // Good
public function getData(): Collection   // Bad, use concrete type
```

## Docblock Standards

### Class Documentation

```php
/**
 * Brief description of the class purpose.
 *
 * Longer description explaining the class behavior, usage, and
 * any important implementation notes. Can span multiple lines.
 *
 * @package Spin\Core
 */
class MyClass
{
}
```

### Property Documentation

```php
class MyClass
{
    /**
     * Brief description of the property.
     *
     * @var string The user's display name
     */
    private string $displayName;

    /**
     * Configuration array with nested options.
     *
     * @var array<string, mixed>
     */
    private array $config;
}
```

### Method Documentation

```php
/**
 * Brief description of what the method does.
 *
 * Longer explanation of the method behavior, side effects,
 * and important usage notes.
 *
 * @param string $name  User's full name
 * @param int $age      User's age in years
 * @param array $tags   Optional tags for categorization
 *
 * @return string The formatted user information
 *
 * @throws InvalidArgumentException If name is empty
 * @throws OutOfRangeException If age is negative
 *
 * @since 0.0.30
 * @see User::validate() For validation rules
 */
public function formatUser(string $name, int $age, array $tags = []): string
{
    if (empty($name)) {
        throw new InvalidArgumentException('Name cannot be empty');
    }
    if ($age < 0) {
        throw new OutOfRangeException('Age cannot be negative');
    }

    return "{$name}, age {$age}";
}
```

### Type Hints in Docblocks

Use proper type hint syntax:

```php
/**
 * @param string[] $names          Array of strings
 * @param array<string, int> $map   Map of string to int
 * @param callable $callback       Callback function
 * @param object|string $mixed     Union type (pre-PHP 8.0)
 * @return \Psr\Http\Message\ResponseInterface PSR-7 Response
 */
public function process(
    array $names,
    array $map,
    callable $callback,
    object|string $mixed
): ResponseInterface {
}
```

## Naming Conventions

### Classes

- **PascalCase** (UpperCamelCase)
- **Descriptive** and **singular** nouns
- **Avoid abbreviations** (except well-known: PDO, JWT, UUID, PSR)

```php
// Correct
class UserController { }
class MySqlDriver { }
class CacheManager { }
class ValidationException { }

// Incorrect
class user_controller { }
class mysql_driver { }
class CacheMgr { }
class InvalidInput { } // Noun should be Validation or Exception
```

### Methods

- **camelCase** (lowerCamelCase)
- **Verb** for action methods
- **Noun** for getter methods
- **is/has** for boolean methods

```php
// Correct
public function handleRequest(): void { }
public function getName(): string { }
public function isActive(): bool { }
public function hasPermission(): bool { }

// Incorrect
public function handle_request(): void { }
public function get_name(): string { }
public function checkActive(): bool { }
```

### Properties

- **camelCase** (lowerCamelCase)
- **Private** by default
- **Prefix with type** intent if helpful

```php
class UserService
{
    // Correct
    private string $name;
    private int $cacheTimeout;
    private array $options;
    private ?Logger $logger = null;

    // Incorrect
    public $name;
    private $mName; // Redundant m prefix with type hint
}
```

### Constants

- **UPPERCASE** with underscores
- **Module-scoped** constants in class

```php
// Correct
const MAX_RETRIES = 3;
const DEFAULT_TIMEOUT = 30;

// Incorrect
const max_retries = 3;
const maxRetries = 3;
```

### Variables

- **camelCase** (lowerCamelCase)
- **Descriptive** names
- **Single letters only** in loops

```php
// Correct
$userName = 'John Doe';
$isActive = true;
$maxRetries = 3;

for ($i = 0; $i < count($items); $i++) {
    $item = $items[$i];
    $processedItem = process($item);
}

// Incorrect
$user_name = 'John Doe';
$mn = 3;
$i = 'variable_name'; // Single letter should be loop counter
```

## Code Review Checklist

Use this checklist when reviewing code:

### Structure & Organization
- [ ] PSR-4 namespace correctly maps to file path
- [ ] File placed in correct directory
- [ ] Related functionality grouped logically
- [ ] No circular dependencies

### Type Safety
- [ ] `declare(strict_types=1);` at top of file
- [ ] All parameters have explicit types
- [ ] All methods have explicit return types
- [ ] No use of `mixed` unless unavoidable
- [ ] Union types used appropriately

### Documentation
- [ ] Class has docblock explaining purpose
- [ ] Public methods documented with `@param` and `@return`
- [ ] Exceptions documented with `@throws`
- [ ] Complex logic has inline comments
- [ ] No obvious typos in documentation

### Naming
- [ ] Classes use PascalCase
- [ ] Methods use camelCase with verb prefix
- [ ] Properties use camelCase
- [ ] Constants use UPPERCASE
- [ ] Names are descriptive and avoid abbreviations

### Style & Formatting
- [ ] 4-space indentation (no tabs)
- [ ] Lines under 120 characters
- [ ] PSR-12 spacing conventions followed
- [ ] Braces positioned correctly
- [ ] No trailing whitespace

### Testing
- [ ] New code has tests
- [ ] Coverage >= 85% for new features
- [ ] Tests verify success and failure cases
- [ ] No skipped or incomplete tests

### Best Practices
- [ ] No magic numbers or strings (use constants)
- [ ] DRY principle followed (no unnecessary duplication)
- [ ] SOLID principles respected
- [ ] Error handling appropriate
- [ ] No debug code or commented-out sections
- [ ] Dependencies are minimal and appropriate

### Backward Compatibility
- [ ] Public APIs not changed without major version
- [ ] New parameters have defaults
- [ ] Deprecation warnings for old methods
- [ ] Breaking changes documented in CHANGELOG.md

## Automated Tools

### PHP-CS-Fixer

Automatically enforce code style:

```bash
# Install
composer require --dev friendsofphp/php-cs-fixer

# Run on all files
./vendor/bin/php-cs-fixer fix src/

# Check without modifying
./vendor/bin/php-cs-fixer fix src/ --dry-run
```

Configuration in `.php-cs-fixer.dist.php`:

```php
$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests']);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'type_declaration_spaces' => true,
    ])
    ->setFinder($finder);
```

### PHPStan

Static analysis for type safety:

```bash
# Install
composer require --dev phpstan/phpstan

# Run analysis
./vendor/bin/phpstan analyse src/

# Generate baseline (to suppress existing issues)
./vendor/bin/phpstan analyse src/ --generate-baseline
```

### PSalm

More advanced static analysis:

```bash
# Install
composer require --dev vimeo/psalm

# Run analysis
./vendor/bin/psalm
```

## Breaking Changes Policy

Breaking changes require:

1. **Major version bump** (e.g., 1.0.0 → 2.0.0)
2. **Documented in CHANGELOG.md** with migration guide
3. **Deprecation period** (if possible) warning old code
4. **Announcement** in release notes
5. **Consider alternatives** before breaking

Example CHANGELOG entry:

```markdown
## [2.0.0] - 2026-04-01

### BREAKING CHANGES
- `Config::get()` now throws `ConfigException` instead of returning null
  - Migrate: Use `Config::get($key, $default)` or catch exception
- Middleware `handle()` signature changed: removed `$next` parameter
  - Migrate: Use middleware chaining in configuration instead

### Deprecated
- `Logger::log()` deprecated in favor of `Logger::info()` (removed in 3.0.0)
```

---

**Last Updated:** 2026-03-15
**Framework Version:** 0.0.36+
