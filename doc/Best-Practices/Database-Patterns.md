# Database Patterns

## Overview

Databases are core to most applications. This guide covers connection management, query patterns, transactions, data validation, and migration strategies that keep data integrity and application performance in sync.

## Connection Management and Pooling

SPIN uses PDO through the `ConnectionManager`. Connections are expensive; reuse them efficiently.

### Single Connection Instance

Create one connection per database and reuse it:

```php
// config-dev.json
{
  "database": {
    "connections": {
      "default": {
        "driver": "mysql",
        "host": "localhost",
        "port": 3306,
        "database": "app_dev",
        "username": "${env:DB_USER}",
        "password": "${env:DB_PASSWORD}"
      }
    }
  }
}

// In your code
$db = app()->getContainer()->get(Connection::class);
```

The container registers a single instance; all dependencies receive the same connection.

### Connection Pooling

For high-traffic apps, consider external pooling:

- **PgBouncer** (PostgreSQL) — connection pooler, reduces backend overhead
- **ProxySQL** (MySQL) — advanced pooling, query routing, caching
- **Redis** (as queue) — offload work to background jobs

```php
// Use pooled connection
$config = config('database');
$db = new PDO(
    $config['pooled_dsn'], // Points to pooler, not database
    $config['username'],
    $config['password']
);
```

### Connection Timeout and Error Handling

Handle connection failures gracefully:

```php
try {
    $result = $db->fetch('SELECT 1');
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Lost connection') !== false) {
        logger()->error('Database disconnected', ['exception' => $e]);
        // Reconnect or queue for retry
        $this->reconnect();
    }
    throw $e;
}
```

## Query Patterns: Raw SQL vs. Query Builders

### Raw SQL (SPIN Default)

SPIN uses parameterized queries with PDO, avoiding injection vulnerabilities:

```php
class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        // Parameterized — safe
        $row = $this->db->fetch(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );
        return $row ? User::fromRow($row) : null;
    }

    public function findByEmailOrPhone(string $email, string $phone): array
    {
        // IN clause with multiple params
        $rows = $this->db->fetchAll(
            'SELECT * FROM users WHERE email = ? OR phone = ?',
            [$email, $phone]
        );
        return array_map(fn($row) => User::fromRow($row), $rows);
    }

    public function findByIds(array $ids): array
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->fetchAll(
            "SELECT * FROM users WHERE id IN ($placeholders)",
            $ids
        );
        return array_map(fn($row) => User::fromRow($row), $rows);
    }
}
```

**Advantages:**
- Direct control, no abstraction overhead
- Easy to write complex queries
- Runs exactly what you intend

**Disadvantages:**
- Repetitive for simple CRUD
- Manual result mapping
- Migration to different databases requires rewriting

### Query Builder (Use If Needed)

If your team prefers a builder, integrate a PSR library like Doctrine or Eloquent:

```php
// Using a hypothetical builder
$user = QueryBuilder::table('users')
    ->where('email', $email)
    ->first();

$users = QueryBuilder::table('users')
    ->where('created_at', '>', date('-30 days'))
    ->orderBy('name')
    ->limit(10)
    ->get();
```

## Transaction Handling

Use transactions to ensure data consistency across multiple operations:

### Basic Transactions

```php
try {
    $this->db->beginTransaction();

    // Deduct from account A
    $this->db->execute(
        'UPDATE accounts SET balance = balance - ? WHERE id = ?',
        [$amount, $fromId]
    );

    // Add to account B
    $this->db->execute(
        'UPDATE accounts SET balance = balance + ? WHERE id = ?',
        [$amount, $toId]
    );

    // Record transfer
    $this->db->execute(
        'INSERT INTO transfers (from_id, to_id, amount) VALUES (?, ?, ?)',
        [$fromId, $toId, $amount]
    );

    $this->db->commit();
} catch (\Throwable $e) {
    $this->db->rollback();
    logger()->error('Transfer failed', ['exception' => $e]);
    throw new TransferFailedException('Transaction rolled back');
}
```

### Savepoints (Nested Transactions)

Some databases support savepoints for complex workflows:

```php
$this->db->beginTransaction();
try {
    $order = $this->createOrder($data);

    $this->db->execute('SAVEPOINT before_items');
    try {
        foreach ($data['items'] as $item) {
            $this->addOrderItem($order->id, $item);
        }
    } catch (\Throwable $e) {
        $this->db->execute('ROLLBACK TO SAVEPOINT before_items');
        // Handle item addition failure
    }

    $this->db->commit();
} catch (\Throwable $e) {
    $this->db->rollback();
    throw $e;
}
```

### Isolation Levels

Choose appropriate isolation levels for your workload:

```php
// MySQL
$this->db->execute('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
$this->db->beginTransaction();
// ... your operations ...
$this->db->commit();
```

| Level | Dirty Reads | Non-Repeatable Reads | Phantoms | Use Case |
|-------|-------------|----------------------|----------|----------|
| READ UNCOMMITTED | Yes | Yes | Yes | Never (data integrity issues) |
| READ COMMITTED | No | Yes | Yes | Default for most (balance consistency vs. concurrency) |
| REPEATABLE READ | No | No | Yes | MySQL default, prevents most anomalies |
| SERIALIZABLE | No | No | No | Critical operations, conflicts with concurrency |

## Data Validation at Database Layer

### Schema Constraints

Enforce invariants in the database:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    age INT NOT NULL CHECK (age >= 18 AND age <= 150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered') DEFAULT 'pending',
    amount DECIMAL(10, 2) NOT NULL CHECK (amount > 0),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Application-Level Validation

Validate before inserting to give users feedback:

```php
class UserService
{
    public function create(array $data): User
    {
        // Validate shape
        if (empty($data['email'])) {
            throw new ValidationException(['email' => 'Email required']);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['email' => 'Invalid email format']);
        }

        // Validate uniqueness
        if ($this->users->findByEmail($data['email'])) {
            throw new ValidationException(['email' => 'Email already registered']);
        }

        // Validate age constraint
        if (($data['age'] ?? null) < 18) {
            throw new ValidationException(['age' => 'Must be 18 or older']);
        }

        // Safe to insert
        return $this->users->save(new User(...$data));
    }
}
```

## Migration Strategies

### Version Control for Schema

Track schema changes in version-controlled migration files:

```php
// migrations/001_create_users.php
return new Migration(
    version: '001',
    name: 'create_users',
    up: function (Connection $db) {
        $db->execute('
            CREATE TABLE users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ');
    },
    down: function (Connection $db) {
        $db->execute('DROP TABLE users');
    }
);
```

### Safe Migrations (Large Tables)

For large tables, plan migrations to minimize locks:

```php
// BAD: Direct column rename locks table
ALTER TABLE users RENAME COLUMN name TO full_name;

// GOOD: Add new, migrate data, remove old
ALTER TABLE users ADD COLUMN full_name VARCHAR(255);
UPDATE users SET full_name = name;
ALTER TABLE users DROP COLUMN name;
```

### Zero-Downtime Deployments

Deploy code and schema changes independently:

1. **Add new column** (backward-compatible)
2. **Deploy new code** that writes to both columns
3. **Backfill data** in background job
4. **Deploy final code** that only reads new column
5. **Drop old column** after verification

## Index Optimization

Indexes trade write performance for query speed:

```sql
-- Fast for common queries
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_orders_user_id_created ON orders(user_id, created_at DESC);

-- Covering index: query answered without touching main table
CREATE INDEX idx_products_active ON products(active) INCLUDE (name, price);

-- Avoid excessive indexes (slow inserts/updates)
```

Monitor slow queries and add indexes as needed:

```php
// Enable slow query log (MySQL)
// SET GLOBAL slow_query_log = 'ON';
// SET GLOBAL long_query_time = 1;
```

## Key Takeaways

1. **Reuse connections** — single instance per database via container
2. **Use parameterized queries** — prevents SQL injection
3. **Wrap multi-step operations in transactions** — ensures consistency
4. **Validate at both layers** — database schema constraints + application logic
5. **Version control migrations** — track schema changes like code
6. **Plan large migrations carefully** — minimize locks, test first
7. **Index strategically** — measure impact, avoid premature indexing
8. **Monitor query performance** — slow logs guide optimization

---

**See also:** [Performance-Optimization.md](Performance-Optimization.md), [Testing-Patterns.md](Testing-Patterns.md), [User-Guide/Databases.md](../User-Guide/Databases.md)
