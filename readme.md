[![Latest Stable Version](https://poser.pugx.org/celarius/spin-framework/v/stable)](https://packagist.org/packages/celarius/spin-framework)
[![Total Downloads](https://poser.pugx.org/celarius/spin-framework/downloads)](https://packagist.org/packages/celarius/spin-framework)
[![License](https://poser.pugx.org/nofuzz/framework/license)](https://packagist.org/packages/celarius/spin-framework)
[![PHP8 Ready](https://img.shields.io/badge/PHP8-ready-green.svg)](https://packagist.org/packages/celarius/spin-framework)
[![Unit Tests](https://github.com/Celarius/spin-framework/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/Celarius/spin-framework/actions/workflows/unit-tests.yml)
[![Code Quality](https://img.shields.io/badge/code%20quality-A-green.svg)](https://github.com/Celarius/spin-framework)
[![Maintenance](https://img.shields.io/badge/maintained-yes-green.svg)](https://github.com/Celarius/spin-framework)

<p align="center">
  <img src="https://via.placeholder.com/400x200/4A90E2/FFFFFF?text=SPIN+Framework" alt="SPIN Framework Logo" width="400">
</p>

<p align="center">
  <strong>A super lightweight, modern PHP framework for building web applications and REST APIs</strong>
</p>

## 🚀 About SPIN Framework

SPIN is a lightweight, high-performance PHP framework designed for building modern web applications and REST APIs. Built with PHP 8+ and following PSR standards, SPIN provides a clean, intuitive foundation for developers who want speed, flexibility, and simplicity without the overhead of larger frameworks.

### ✨ Why Choose SPIN?

- **🚀 Lightning Fast** - Minimal overhead, optimized for performance
- **🔧 PSR Compliant** - Built on industry standards for maximum compatibility
- **📱 Modern PHP 8+** - Leverages the latest PHP features and performance improvements
- **🔄 Flexible Architecture** - Easy to extend and customize for your specific needs
- **📚 Comprehensive** - Built-in support for routing, middleware, caching, databases, and more
- **🌐 Platform Agnostic** - Works seamlessly on Windows, Linux, and macOS

## 📋 Requirements

- **PHP**: 8.0 or higher
- **Extensions**: PDO, JSON, OpenSSL, Mbstring
- **Web Server**: Apache, Nginx, or any PSR-7 compatible server
- **Database**: MySQL, PostgreSQL, SQLite, CockroachDB, Firebird, or any PDO-compatible database

## 🛠️ Installation

### Quick Start with Composer

```bash
composer require celarius/spin-framework
```

### Using the SPIN Skeleton (Recommended)

For the best development experience, start with our official skeleton project:

```bash
# Clone the skeleton
git clone https://github.com/Celarius/spin-skeleton.git my-spin-app
cd my-spin-app

# Install dependencies
composer install

# Start development server
php -S localhost:8000 -t src/public
```

## 🏗️ Project Structure

```
my-spin-app/
├── src/
│   ├── app/
│   │   ├── Config/           # Configuration files
│   │   ├── Controllers/      # Controllers
│   │   ├── Middlewares/      # Custom middleware
│   │   ├── Views/            # Template files
│   │   └── Globals.php       # Global functions
│   ├── public/               # Web root directory
│   │   ├── bootstrap.php     # Application entry point
│   └── storage/              # Application storage
│       ├── logs/             # Log files
│       ├── cache/            # Optional. Cache files
├── vendor/                   # Composer dependencies
├── composer.json             # Project dependencies
```

## 🚀 Getting Started

### 1. Configuration

SPIN uses JSON-based configuration files, in the `/Config` folder, named after the environment `config-<env>.json`:

```json
{
  "application": {
    "global": {
      "maintenance": false,
      "timezone": "Europe/Stockholm"
    },
    "secret": "${env:APPLICATION_SECRET}"
  },
  "session": {
    "cookie": "SID",
    "timeout": 3600,
    "driver": "apcu"
  },
  "logger": {
    "level": "notice",
    "driver": "php"
  }
}
```
_Configuration files support `${env:<varName>}` macros for environment variables in values_

### 2. Routing

Routes are defined in JSON configuration files:

```json
{
  "common": {
    "before": ["\\App\\Middlewares\\RequestIdBeforeMiddleware"],
    "after": ["\\App\\Middlewares\\ResponseLogAfterMiddleware"]
  },
  "groups": [
    {
      "name": "Public API",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\CorsBeforeMiddleware"],
      "routes": [
        { "methods": ["GET"], "path": "/health", "handler": "\\App\\Controllers\\Api\\HealthController" }
      ]
    },
    {
      "name": "Protected API",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\AuthHttpBeforeMiddleware"],
      "routes": [
        { "methods": ["GET"], "path": "/users/{id}", "handler": "\\App\\Controllers\\Api\\UserController" }
      ]
    }
  ]
}
```

### 3. Controllers

Controllers extend SPIN's base classes and use specific HTTP method handlers:

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use \App\Controllers\AbstractPlatesController;

class IndexController extends AbstractPlatesController
{
    public function handleGET(array $args)
    {
        $model = ['title' => 'Welcome to SPIN', 'user' => 'Guest'];
        $html = $this->engine->render('pages::index', $model);
        return response($html);
    }
}
```

### 4. Middleware

Middleware extends `Spin\Core\Middleware`:

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class AuthMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        $this->secret = config('application.secret');
        return true;
    }

    public function handle(array $args): bool
    {
        $token = getRequest()->getHeaderLine('Authorization');
        if (!$this->validateToken($token)) {
            responseJson(['error' => 'Unauthorized'], 401);
            return false;
        }
        return true;
    }
}
```

## 🔧 Core Features

### 🛣️ JSON-Based Routing
- **Route Groups** - Organize routes with shared middleware and prefixes
- **HTTP Method Support** - Full support for GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS
- **Dynamic Parameters** - Capture URL parameters with `{paramName}` syntax
- **Middleware Integration** - Apply middleware at common, group, or route level

### 🔌 Middleware System
- **Common Middleware** - Applied to all requests globally
- **Group Middleware** - Applied to specific route groups
- **Route Middleware** - Applied to individual routes
- **SPIN-Specific** - Uses `initialize()` and `handle()` methods

### 🗄️ Database Support
- **Multiple Drivers** - MySQL, PostgreSQL, SQLite, CockroachDB, Firebird
- **PDO Based** - Secure, prepared statements by default
- **Connection Management** - Efficient database connection handling
- **JSON Configuration** - Database settings in configuration files

### 💾 Caching
- **PSR-16 Compatible** - Standard cache interface
- **Multiple Adapters** - APCu, Redis, File-based caching
- **JSON Configuration** - Cache settings in configuration files
- **Performance Optimized** - Minimal overhead for maximum speed

### 📁 File Management
- **Secure Uploads** - Built-in security and validation
- **Multiple Storage Backends** - Local, cloud, or custom storage
- **File Processing** - Image manipulation, document processing
- **Access Control** - Fine-grained permissions and security

## 📚 Documentation

Complete documentation is available in the [**doc/** directory](doc/README.md). Quick navigation:

| Topic | Resources |
|-------|-----------|
| **Getting Started** | [Quick Start](doc/Getting-Started/Quick-Start.md) • [Core Concepts](doc/Getting-Started/Core-Concepts.md) • [Your First App](doc/Getting-Started/Your-First-App.md) |
| **User Guide** | [Configuration](doc/User-Guide/Configuration.md) • [Routing](doc/User-Guide/Routing.md) • [Middleware](doc/User-Guide/Middleware.md) • [Databases](doc/User-Guide/Databases.md) • [Cache](doc/User-Guide/Cache.md) |
| **Development** | [Security](doc/User-Guide/Security.md) • [Testing](doc/User-Guide/Testing.md) • [Helpers](doc/User-Guide/Helpers.md) • [File Uploads](doc/User-Guide/Uploaded-files.md) |
| **Best Practices** | [Application Design](doc/Best-Practices/Application-Design.md) • [Error Handling](doc/Best-Practices/Error-Handling.md) • [Performance](doc/Best-Practices/Performance-Optimization.md) • [Database Patterns](doc/Best-Practices/Database-Patterns.md) |
| **Recipes & Examples** | [Authentication](doc/Recipes/Authentication.md) • [CORS Handling](doc/Recipes/CORS-Handling.md) • [Rate Limiting](doc/Recipes/Rate-Limiting.md) • [Deployment](doc/Recipes/Deployment.md) |
| **API Reference** | [Core Classes & Functions](doc/Reference/API-Reference.md) |

Browse [doc/README.md](doc/README.md) for the complete documentation index.

## 🧪 Testing

### Run Tests

```bash
# Windows
.\phpunit.cmd

# Linux/macOS
./vendor/bin/phpunit

# With coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

### Test Structure

```
tests/
├── Unit/              # Unit tests
├── Integration/       # Integration tests
├── Feature/           # Feature tests
└── bootstrap.php      # Test bootstrap
```

## 🌐 Web Server Configuration

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName mydomain.com
    DocumentRoot "/path/to/your/app/src/public"

    <Directory "/path/to/your/app/src/public">
        AllowOverride All
        Require all granted

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ bootstrap.php [QSA,L]
    </Directory>
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name mydomain.com;
    root /path/to/your/app/src/public;
    index bootstrap.php;

    location / {
        try_files $uri $uri/ /bootstrap.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index bootstrap.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔌 PSR Standards Support

SPIN Framework is built on PSR standards for maximum compatibility:

- **PSR-3** - Logger Interface (Monolog by default)
- **PSR-7** - HTTP Message Interface (Guzzle by default)
- **PSR-11** - Container Interface (League Container by default)
- **PSR-15** - HTTP Middleware Interface
- **PSR-16** - Simple Cache Interface
- **PSR-17** - HTTP Factory Interface

## 🚀 Performance Features

- **Lazy Loading** - Components loaded only when needed
- **Memory Management** - Efficient memory usage and garbage collection
- **Connection Pooling** - Optimized database connections
- **Smart Caching** - Intelligent cache invalidation and management
- **Compiled Routes** - Fast route matching and resolution

## 🔒 Security Features

- **CSRF Protection** - Built-in cross-site request forgery protection
- **SQL Injection Prevention** - PDO prepared statements by default
- **XSS Protection** - Automatic output escaping
- **File Upload Security** - Secure file handling and validation
- **Input Validation** - Comprehensive input sanitization
- **JWT Support** - Built-in JWT token handling
- **Rate Limiting** - Built-in request rate limiting

## 🌟 Community & Support

### Getting Help

- **Documentation**: [https://github.com/Celarius/spin-framework](https://github.com/Celarius/spin-framework)
- **Issues**: [GitHub Issues](https://github.com/Celarius/spin-framework/issues)
- **Discussions**: [GitHub Discussions](https://github.com/Celarius/spin-framework/discussions)

### Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

### Code of Conduct

Please read our [Code of Conduct](CODE_OF_CONDUCT.md) to keep our community approachable and respectable.

## 📄 License

SPIN Framework is open-sourced software licensed under the [MIT License](LICENSE).

## 🙏 Acknowledgments

- Built with ❤️ by the SPIN Framework Team
- Inspired by modern PHP frameworks and PSR standards
- Special thanks to all contributors and the PHP community

## 📊 Statistics

- **Downloads**: [![Total Downloads](https://poser.pugx.org/celarius/spin-framework/downloads)](https://packagist.org/packages/celarius/spin-framework)
- **Version**: [![Latest Stable Version](https://poser.pugx.org/celarius/spin-framework/v/stable)](https://packagist.org/packages/celarius/spin-framework)
- **Tests**: [![Unit Tests](https://github.com/Celarius/spin-framework/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/Celarius/spin-framework/actions/workflows/unit-tests.yml)

---

**Ready to build something amazing?** Start with SPIN Framework today and experience the joy of lightweight, fast PHP development! 🚀
