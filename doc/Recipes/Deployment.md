# Deployment Recipe

## Problem

How do I deploy my SPIN Framework application to production safely and reliably?

This guide covers environment configuration, database management, health checks, zero-downtime deployment, and production checklist.

---

## Solution

Deploy SPIN applications using environment-based configuration, database migrations, health checks, and blue-green deployment patterns for zero downtime.

---

## Environment Configuration

### Configuration File Structure

```
config/
├── config-local.json          # Development (not committed)
├── config-development.json    # Dev environment
├── config-staging.json        # Staging environment
└── config-production.json     # Production environment
```

### Configuration with Environment Variables

```json
{
  "app": {
    "name": "My API",
    "environment": "${env:APP_ENV:production}",
    "debug": "${env:APP_DEBUG:false}",
    "key": "${env:APP_KEY}",
    "log_level": "${env:LOG_LEVEL:info}"
  },
  "database": {
    "driver": "${env:DB_DRIVER:pdo}",
    "host": "${env:DB_HOST:localhost}",
    "port": "${env:DB_PORT:5432}",
    "database": "${env:DB_NAME}",
    "username": "${env:DB_USER}",
    "password": "${env:DB_PASSWORD}",
    "ssl": "${env:DB_SSL:false}"
  },
  "cache": {
    "driver": "${env:CACHE_DRIVER:redis}",
    "host": "${env:REDIS_HOST:localhost}",
    "port": "${env:REDIS_PORT:6379}"
  },
  "session": {
    "driver": "${env:SESSION_DRIVER:database}",
    "lifetime": "${env:SESSION_LIFETIME:3600}"
  }
}
```

### Secrets Management

Never commit `.env` files. Use environment variables or secret management:

```bash
# Using .env (git-ignored — SPIN loads this automatically at startup)
APP_ENV=production
APP_KEY=your-secret-key-here
APP_DEBUG=false
DB_HOST=db.example.com
DB_USER=appuser
DB_PASSWORD=super-secret-password
JWT_SECRET=very-long-random-secret-key-min-32-chars
```

SPIN loads `.env` from the project root before config files are parsed, so all variables
defined there are available as `${env:VAR}` macros. Real environment variables (set by
the OS, Docker, or CI) always take precedence — `.env` is never applied over an
already-set variable.

Production secret management options:

```php
<?php
// AWS Secrets Manager
$secret = json_decode(
    file_get_contents('php://stdin'),
    true
);
putenv('DB_PASSWORD=' . $secret['password']);

// Vault (HashiCorp)
$ch = curl_init('https://vault.example.com/v1/secret/my-app');
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BEARER);
curl_setopt($ch, CURLOPT_USERPWD, env('VAULT_TOKEN'));
$secret = json_decode(curl_exec($ch), true);

// Docker secrets
$secret = file_get_contents('/run/secrets/db_password');
putenv('DB_PASSWORD=' . trim($secret));
```

---

## Database Setup and Migrations

### Migration Structure

```php
<?php
declare(strict_types=1);
// migrations/001_create_users_table.php

class CreateUsersTable
{
    public function up(): void
    {
        db()->statement("
            CREATE TABLE users (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        db()->statement("
            CREATE INDEX idx_users_email ON users(email)
        ");
    }

    public function down(): void
    {
        db()->statement("DROP TABLE IF EXISTS users");
    }
}
```

### Migration Runner

```php
<?php
declare(strict_types=1);
namespace App\Commands;

class MigrateCommand
{
    public function handle(array $args): void
    {
        $direction = $args[0] ?? 'up';
        $steps = (int)($args[1] ?? 1);

        $migrationsDir = base_path('migrations');
        $files = glob($migrationsDir . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $className = basename($file, '.php');
            require_once $file;

            $migration = new $className();

            if ($direction === 'up') {
                echo "Migrating: $className\n";
                $migration->up();
            } else {
                echo "Rolling back: $className\n";
                $migration->down();
            }
        }

        echo "Migration complete.\n";
    }
}

// Usage
// php artisan migrate up
// php artisan migrate down 1
```

### Schema Initialization

```bash
#!/bin/bash
# deploy/init-db.sh

set -e

echo "Waiting for database..."
./deploy/wait-for-db.sh

echo "Running migrations..."
php bin/console migrate up

echo "Seeding database..."
php bin/console seed

echo "Database initialized."
```

---

## Application Startup

### Health Check Handler

```php
<?php
declare(straight_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class HealthCheckController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'filesystem' => $this->checkFilesystem(),
        ];

        $healthy = !in_array(false, array_column($checks, 'ok'));
        $status = $healthy ? 200 : 503;

        return responseJson([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'checks' => $checks,
        ], $status);
    }

    private function checkApp(): array
    {
        return [
            'ok' => true,
            'message' => 'Application running',
        ];
    }

    private function checkDatabase(): array
    {
        try {
            db()->select("SELECT 1");
            return ['ok' => true, 'message' => 'Database connected'];
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            cache()->set('health-check', time(), 10);
            cache()->get('health-check');
            cache()->delete('health-check');

            return ['ok' => true, 'message' => 'Cache working'];
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'message' => 'Cache error: ' . $e->getMessage(),
            ];
        }
    }

    private function checkFilesystem(): array
    {
        $uploadDir = storage_path('uploads');
        if (!is_writable($uploadDir)) {
            return [
                'ok' => false,
                'message' => 'Upload directory not writable',
            ];
        }

        return ['ok' => true, 'message' => 'Filesystem writable'];
    }
}
```

Route configuration:

```json
{
  "routes": [
    {
      "path": "/health",
      "method": "GET",
      "controller": "HealthCheckController",
      "handler": "handleGET",
      "middleware": []
    },
    {
      "path": "/ready",
      "method": "GET",
      "controller": "ReadinessController",
      "handler": "handleGET",
      "middleware": []
    }
  ]
}
```

### Readiness Check

```php
<?php
declare(strict_types=1);
namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class ReadinessController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        // Check if application is ready to accept traffic
        try {
            // Verify database migrations are current
            $latestMigration = db()->table('migrations')
                                   ->orderBy('id', 'DESC')
                                   ->first();

            if (!$latestMigration) {
                return responseJson([
                    'ready' => false,
                    'message' => 'Database not initialized',
                ], 503);
            }

            // Verify required cache is available
            cache()->set('readiness-check', 1, 1);

            return responseJson([
                'ready' => true,
                'timestamp' => date('c'),
            ], 200);

        } catch (\Exception $e) {
            logger()->error('Readiness check failed: ' . $e->getMessage());

            return responseJson([
                'ready' => false,
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}
```

---

## Docker Deployment

### Dockerfile

```dockerfile
FROM php:8.2-fpm-alpine

WORKDIR /app

# Install dependencies
RUN apk add --no-cache \
    postgresql-dev \
    redis \
    curl \
    git \
    && docker-php-ext-install pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . /app

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Create storage directory
RUN mkdir -p storage/uploads && chmod 755 storage/uploads

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
```

### Docker Compose

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      APP_ENV: production
      DB_HOST: postgres
      REDIS_HOST: redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8000/health"]
      interval: 30s
      timeout: 10s
      retries: 3

  postgres:
    image: postgres:15-alpine
    environment:
      POSTGRES_DB: ${DB_NAME}
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - pgdata:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USER}"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  pgdata:
```

---

## Zero-Downtime Deployment

### Blue-Green Deployment Strategy

```bash
#!/bin/bash
# deploy/blue-green.sh

set -e

BLUE_CONTAINER="app-blue"
GREEN_CONTAINER="app-green"
LOAD_BALANCER="localhost:8000"

# Determine which is active
ACTIVE=$(curl -s $LOAD_BALANCER/active-container)

if [ "$ACTIVE" = "blue" ]; then
    DEPLOY_TO="green"
    CURRENT="blue"
else
    DEPLOY_TO="blue"
    CURRENT="green"
fi

echo "Deploying to $DEPLOY_TO (current: $CURRENT)"

# Stop old container
docker stop $DEPLOY_TO || true
docker rm $DEPLOY_TO || true

# Build and start new container
docker build -t spin-app:latest .
docker run -d \
    --name $DEPLOY_TO \
    -p 9000:8000 \
    -e APP_ENV=production \
    spin-app:latest

# Wait for health check to pass
echo "Waiting for container to be healthy..."
for i in {1..30}; do
    if docker exec $DEPLOY_TO curl -f http://localhost:8000/health > /dev/null; then
        echo "Container is healthy"
        break
    fi
    echo "Waiting... ($i/30)"
    sleep 2
done

# Switch load balancer
echo "Switching traffic to $DEPLOY_TO..."
docker exec load-balancer \
    curl -X POST http://localhost:8080/switch \
    -d "container=$DEPLOY_TO"

echo "Deployment complete"

# Keep old container for rollback
echo "Old container ($CURRENT) kept for rollback"
```

### Rolling Deployment

```bash
#!/bin/bash
# deploy/rolling.sh

set -e

INSTANCES=3
HEALTHY_THRESHOLD=1

for i in $(seq 1 $INSTANCES); do
    CONTAINER="app-instance-$i"

    echo "Deploying to $CONTAINER..."

    # Update container
    docker pull spin-app:latest
    docker stop $CONTAINER
    docker run -d --name $CONTAINER-new spin-app:latest

    # Wait for health
    sleep 10
    if docker exec $CONTAINER-new curl -f http://localhost:8000/health; then
        # Remove old container
        docker rm $CONTAINER
        docker rename $CONTAINER-new $CONTAINER
        echo "$CONTAINER updated successfully"
    else
        echo "Health check failed, rolling back $CONTAINER"
        docker rm $CONTAINER-new
        docker start $CONTAINER
        exit 1
    fi
done

echo "Rolling deployment complete"
```

---

## Production Checklist

### Pre-Deployment

- [ ] All tests passing (`./vendor/bin/phpunit`)
- [ ] Code review completed and approved
- [ ] Database migrations tested locally
- [ ] Configuration for production environment prepared
- [ ] Secrets stored in vault/secret manager
- [ ] SSL certificates valid
- [ ] Load balancer configured
- [ ] Monitoring and logging configured

### Application

- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Error pages configured (don't expose stack traces)
- [ ] Logging configured to file/external service
- [ ] Request ID tracking enabled
- [ ] Rate limiting configured
- [ ] CORS policies set correctly
- [ ] Security headers enabled
- [ ] Dependencies up to date

### Database

- [ ] Backups configured and tested
- [ ] Connection pooling configured
- [ ] Query optimization completed
- [ ] Indexes created for common queries
- [ ] Replication/failover configured
- [ ] Monitoring configured

### Infrastructure

- [ ] Load balancer health checks configured
- [ ] Auto-scaling policies set
- [ ] Firewall rules configured
- [ ] VPN/private networking for databases
- [ ] CDN configured for static assets
- [ ] Backup storage configured
- [ ] Disaster recovery plan documented

### Monitoring & Alerting

- [ ] Application error alerting configured
- [ ] Database performance alerts
- [ ] Disk space alerts
- [ ] Memory usage alerts
- [ ] API latency monitoring
- [ ] Uptime monitoring
- [ ] Log aggregation configured

### Security

- [ ] HTTPS enforced
- [ ] Security headers configured
- [ ] SQL injection protections verified
- [ ] XSS protection enabled
- [ ] CSRF tokens configured
- [ ] Rate limiting active
- [ ] File upload validation
- [ ] Access controls verified
- [ ] Secrets not in code/logs

---

## Rollback Procedure

```bash
#!/bin/bash
# deploy/rollback.sh

set -e

VERSION=$1  # e.g., "v1.2.3"

if [ -z "$VERSION" ]; then
    echo "Usage: ./rollback.sh <version>"
    exit 1
fi

echo "Rolling back to version $VERSION"

# Switch to old container/tag
docker pull spin-app:$VERSION
docker stop app
docker run -d --name app spin-app:$VERSION

# Run migrations if needed
docker exec app php bin/console migrate up

# Verify health
if docker exec app curl -f http://localhost:8000/health; then
    echo "Rollback successful"
else
    echo "Rollback health check failed"
    exit 1
fi
```

---

## Monitoring & Observability

```php
<?php
declare(strict_types=1);
// Configure logging

$logConfig = [
    'name' => 'spin-app',
    'handlers' => [
        [
            'type' => 'stream',
            'path' => env('LOG_PATH', 'php://stdout'),
            'level' => env('LOG_LEVEL', 'info'),
        ],
        [
            'type' => 'syslog',
            'ident' => 'spin-app',
            'facility' => LOG_LOCAL0,
        ],
    ],
];

// Log application events
logger()->info('Application started', [
    'version' => env('APP_VERSION'),
    'environment' => env('APP_ENV'),
    'timestamp' => date('c'),
]);

// Request/response logging
logger()->info('Request processed', [
    'method' => getRequest()->getMethod(),
    'path' => getRequest()->getUri()->getPath(),
    'status' => getResponse()->getStatusCode(),
    'duration_ms' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
    'memory_mb' => memory_get_usage(true) / 1024 / 1024,
]);
```

---

## Production Configuration Example

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=your-very-long-secret-key-min-32-characters
APP_VERSION=1.0.0

# Database
DB_DRIVER=pdo
DB_HOST=db.example.com
DB_PORT=5432
DB_NAME=production_db
DB_USER=app_user
DB_PASSWORD=very-secure-password-from-vault
DB_SSL=true

# Cache
CACHE_DRIVER=redis
REDIS_HOST=cache.example.com
REDIS_PORT=6379
REDIS_TIMEOUT=10

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=3600

# Logging
LOG_LEVEL=info
LOG_PATH=/var/log/spin-app/application.log

# Security
JWT_SECRET=long-random-secret-from-vault
CORS_ENABLED=true
CORS_ALLOWED_ORIGINS=https://app.example.com,https://api.example.com
RATE_LIMIT_ENABLED=true
RATE_LIMIT_WINDOW_SECONDS=60
RATE_LIMIT_DEFAULT_REQUESTS=100
```

---

## Best Practices

1. **Automate everything** - Deployments should be reproducible and auditable
2. **Test in staging** - Run full test suite in staging before production
3. **Use feature flags** - Deploy code before enabling features
4. **Monitor constantly** - Watch metrics and logs in real-time
5. **Plan rollbacks** - Have a quick rollback procedure ready
6. **Gradual rollout** - Deploy to small subset first, then expand
7. **Zero-downtime deploys** - Use blue-green or rolling deployments
8. **Secure secrets** - Never store secrets in code or logs
9. **Keep migrations safe** - Test down/up migrations thoroughly
10. **Document everything** - Maintain runbooks for common procedures

---

## Related Documentation

- [User-Guide: Configuration](../User-Guide/Configuration.md)
- [Best-Practices: Security](../Best-Practices/Security.md)
- [Best-Practices: Performance](../Best-Practices/Performance.md)
