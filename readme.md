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
php -S localhost:8000 -t public
```

## 🏗️ Project Structure

```
my-spin-app/
├── app/
│   ├── Config/           # Configuration files
│   ├── Controllers/      # Application controllers
│   ├── Middleware/       # Custom middleware
│   └── Models/          # Data models
├── public/               # Web root directory
│   ├── bootstrap.php     # Application entry point
│   ├── index.php         # Fallback entry point
│   └── assets/          # CSS, JS, images
├── storage/              # Application storage
│   ├── logs/            # Log files
│   ├── cache/           # Cache files
│   └── uploads/         # Uploaded files
├── vendor/               # Composer dependencies
├── composer.json         # Project dependencies
└── .env                  # Environment variables
```

## 🚀 Getting Started

### 1. Create Your First Route

```php
<?php
// app/Config/routes.php

return [
    'routes' => [
        [
            'path' => '/',
            'handler' => 'HomeController@index',
            'methods' => ['GET']
        ],
        [
            'path' => '/api/users',
            'handler' => 'UserController@list',
            'methods' => ['GET'],
            'middleware' => ['auth']
        ]
    ]
];
```

### 2. Create a Controller

```php
<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class HomeController extends Controller
{
    public function index(): ResponseInterface
    {
        return response()->json([
            'message' => 'Welcome to SPIN Framework!',
            'version' => '1.0.0'
        ]);
    }
}
```

### 3. Add Middleware

```php
<?php
// app/Middleware/AuthMiddleware.php

namespace App\Middleware;

use Spin\Core\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthMiddleware extends Middleware
{
    public function process(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        // Your authentication logic here
        if (!$this->isAuthenticated($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return $next($request, $response);
    }
}
```

## 🔧 Core Features

### 🛣️ Advanced Routing
- **Route Groups** - Organize routes with shared middleware and prefixes
- **HTTP Method Support** - Full support for GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS
- **Dynamic Parameters** - Capture URL parameters with type validation
- **Middleware Integration** - Apply middleware to individual routes or groups

### 🔌 Middleware System
- **Global Middleware** - Applied to all requests
- **Route Middleware** - Applied to specific routes or groups
- **PSR-15 Compatible** - Follows industry standards for maximum compatibility
- **Easy to Extend** - Simple interface for creating custom middleware

### 🗄️ Database Support
- **Multiple Drivers** - MySQL, PostgreSQL, SQLite, CockroachDB, Firebird
- **PDO Based** - Secure, prepared statements by default
- **Connection Pooling** - Efficient database connection management
- **Query Builder** - Fluent interface for building complex queries

### 💾 Caching
- **PSR-16 Compatible** - Standard cache interface
- **Multiple Adapters** - APCu, Redis, File-based caching
- **Automatic Invalidation** - Smart cache management
- **Performance Optimized** - Minimal overhead for maximum speed

### 📁 File Management
- **Secure Uploads** - Built-in security and validation
- **Multiple Storage Backends** - Local, cloud, or custom storage
- **File Processing** - Image manipulation, document processing
- **Access Control** - Fine-grained permissions and security

## 📚 Documentation

### Core Concepts
- **[Routing & Controllers](doc/Routing.md)** - Learn how to handle HTTP requests
- **[Middleware](doc/Middleware.md)** - Understand the middleware pipeline
- **[Database Operations](doc/Databases.md)** - Working with databases
- **[Caching](doc/Cache.md)** - Implementing efficient caching strategies
- **[File Uploads](doc/Uploaded-files.md)** - Secure file handling
- **[Storage Management](doc/Storage-folders.md)** - Managing application storage

### Advanced Topics
- **[Configuration Management](doc/Configuration.md)** - Application configuration
- **[Logging & Debugging](doc/Logging.md)** - Application monitoring
- **[Security Best Practices](doc/Security.md)** - Security guidelines
- **[Performance Optimization](doc/Performance.md)** - Speed optimization tips
- **[Testing](doc/Testing.md)** - Unit and integration testing

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
    DocumentRoot "/path/to/your/app/public"
    
    <Directory "/path/to/your/app/public">
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
    root /path/to/your/app/public;
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
