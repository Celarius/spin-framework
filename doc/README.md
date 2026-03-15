# SPIN Framework Documentation

Welcome to the SPIN Framework documentation hub. This is your central navigation point for learning the framework, building applications, and contributing to the project.

## Quick Navigation

| Section | Purpose | Best For |
|---------|---------|----------|
| **[Getting Started](#getting-started)** | Installation, first app, core concepts | New users |
| **[User Guide](#user-guide)** | Feature-by-feature deep dives | Building applications |
| **[Best Practices](#best-practices)** | Design patterns, performance, security | Intermediate to advanced |
| **[Recipes](#recipes)** | Code examples, common patterns, tutorials | Problem-solving |
| **[Contributor Guide](#contributor-guide)** | Development setup, testing, contributing | Contributors |
| **[Reference](#reference)** | API docs, configuration schemas, glossary | Quick lookup |

---

## Getting Started

### For Complete Beginners

Start here if you're new to SPIN Framework or web framework development in general.

- **What is SPIN?** — A lightweight PHP 8+ web framework for building web apps and REST APIs with minimal overhead
- **Installation** — Get SPIN installed in your project
- **Your First App** — Build a simple "Hello World" application
- **Core Concepts** — Request/response lifecycle, routing, controllers, middleware

*Estimated reading time: 20-30 minutes*

**Related:** See [Getting-Started/](Getting-Started/) directory

---

## User Guide

Comprehensive documentation of SPIN Framework features. Each guide covers one major feature area with examples and best practices.

### Core Features

- **[Configuration.md](User-Guide/Configuration.md)** — Environment-based config files, variable expansion, reading at runtime
- **[Routing.md](User-Guide/Routing.md)** — JSON-based route definitions, route groups, parameter binding, HTTP methods
- **[Middleware.md](User-Guide/Middleware.md)** — Request/response pipeline, global and group-level middleware, short-circuiting

### Building Applications

- **[Databases.md](User-Guide/Databases.md)** — PDO drivers, connection pooling, query execution, transactions
- **[Cache.md](User-Guide/Cache.md)** — Caching strategies, adapters (Redis, APCu, File), cache invalidation
- **[Helpers.md](User-Guide/Helpers.md)** — Global helper functions, utilities, response builders, JWT/JWE, UUID generation

### Advanced Features

- **[Uploaded-files.md](User-Guide/Uploaded-files.md)** — File upload handling, validation, storage
- **[Storage-folders.md](User-Guide/Storage-folders.md)** — Organizing file storage, paths, accessibility
- **[Security.md](User-Guide/Security.md)** — Authentication, authorization, CSRF, input validation, encryption
- **[Testing.md](User-Guide/Testing.md)** — PHPUnit setup, writing tests, mocking, integration testing

**Recommended reading order:**
1. Configuration (project setup)
2. Routing (how requests map to code)
3. Middleware (request processing)
4. Databases (data persistence)
5. Security (protecting your app)
6. Then follow your specific needs

**Related:** See [User-Guide/](User-Guide/) directory

---

## Best Practices

Design patterns, architectural decisions, and optimization guidelines developed from real-world experience with the SPIN Framework.

### Code Quality

- **Error Handling** — Exception strategies, error responses, logging
- **Type Safety** — Leveraging PHP's type system, strict typing requirements
- **Code Organization** — Structure, namespacing, class responsibilities
- **Performance Optimization** — Caching strategies, query optimization, profiling

### Architecture

- **Middleware Design** — Building composable, reusable middleware components
- **Controller Organization** — Single responsibility, testing, code reuse
- **Testing Strategy** — Test coverage goals, integration vs. unit tests, mocking strategies
- **Configuration Management** — Secrets handling, environment-specific configs, validation

### Security

- **Authentication Flows** — JWT patterns, session management, refresh tokens
- **Authorization Models** — Role-based access control (RBAC), permission validation
- **Input Validation** — Sanitization, type checking, allowlisting
- **CORS and HTTPS** — Cross-origin requests, secure headers

### API Design

- **REST Principles** — Resource modeling, HTTP method selection, status codes
- **API Versioning** — Versioning strategies, backward compatibility
- **Error Responses** — Consistent error formats, documentation
- **Documentation** — OpenAPI/Swagger, endpoint examples, client SDKs

**Related:** See [Best-Practices/](Best-Practices/) directory

---

## Recipes

Practical code examples and step-by-step guides for common development tasks. Each recipe is self-contained and ready to adapt to your needs.

### Application Setup

- **Creating Your First API** — Scaffold, endpoints, response formats
- **Building a Multi-Environment App** — Configs per environment, secrets management
- **Adding Authentication** — JWT setup, login endpoints, protected routes
- **Setting Up Logging** — Application logging, request tracking, error reporting

### Database Recipes

- **Database Migrations** — Schema versioning, migrations, rollbacks
- **Connection Pooling** — Multiple connections, failover, load balancing
- **Caching Query Results** — Cache keys, invalidation, warming

### API Development

- **Paginating Results** — Offset/limit, cursor-based, sorting
- **Rate Limiting** — Protecting endpoints, quota management
- **File Upload APIs** — Accepting uploads, validation, storage
- **Webhooks** — Sending events, retries, security

### Deployment

- **Docker Containerization** — Dockerfile, compose setup, container config
- **CI/CD Integration** — GitHub Actions, testing on push, automated deploy
- **Performance Tuning** — Caching, compression, database optimization
- **Monitoring and Observability** — Logging, metrics, error tracking

**Related:** See [Recipes/](Recipes/) directory

---

## Contributor Guide

Documentation for developers contributing to SPIN Framework itself. Learn how to set up your environment, run tests, and submit contributions.

### Getting Started as a Contributor

- **Development Setup** — Cloning, dependencies, IDE configuration
- **Running Tests** — PHPUnit, coverage, debugging tests
- **Code Style** — PSR-12, naming conventions, documentation standards
- **Commit Conventions** — Message format, atomic commits, no merge commits

### Contributing Changes

- **Submitting a Pull Request** — Branch strategy, PR description, code review process
- **Breaking Changes** — Impact assessment, deprecation periods, changelog entries
- **Adding Features** — Design discussion, RFC process, testing requirements
- **Bug Fixes** — Investigation, test coverage, backporting

### Project Structure

- **Directory Organization** — Where code lives, how it's organized
- **Dependency Management** — Adding/updating packages, version constraints
- **Documentation** — Keeping docs in sync, building the doc site
- **Release Process** — Version numbering, changelog, tagging

**Related:** See [Contributor-Guide/](Contributor-Guide/) directory

---

## Reference

Quick-lookup documentation for specific topics. Use when you know what you're looking for.

### API Reference

- **Core Classes** — `Application`, `Controller`, `Middleware`, `Route`, `Request`, `Response`
- **Helpers** — Function signatures, parameters, return values
- **Exceptions** — Exception types, error codes, handling
- **Configuration Schema** — All config keys, types, defaults, examples

### Guides by Purpose

- **Common Tasks** — "How do I...?" index
- **Glossary** — Terms, acronyms, concepts
- **FAQ** — Frequently asked questions
- **Troubleshooting** — Common problems and solutions

### External References

- **PHP Documentation** — [php.net](https://www.php.net)
- **PSR Standards** — [PSR-3 (Logging)](https://www.php-fig.org/psr/psr-3/), [PSR-7 (HTTP)](https://www.php-fig.org/psr/psr-7/), [PSR-11 (Container)](https://www.php-fig.org/psr/psr-11/), [PSR-16 (Cache)](https://www.php-fig.org/psr/psr-16/), [PSR-17 (HTTP Factories)](https://www.php-fig.org/psr/psr-17/)
- **FastRoute** — [nikic/fast-route](https://github.com/nikic/FastRoute)
- **Monolog** — [monolog/monolog](https://github.com/Seldaek/monolog)
- **PHP-JWT** — [firebase/php-jwt](https://github.com/firebase/php-jwt)

**Related:** See [Reference/](Reference/) directory

---

## Reading by Audience

### I'm New to Web Development

1. Read: [User Guide → Routing.md](User-Guide/Routing.md) — Understand how requests work
2. Read: [User Guide → Configuration.md](User-Guide/Configuration.md) — Learn to configure apps
3. Try: Recipe "Creating Your First API"
4. Read: [User Guide → Security.md](User-Guide/Security.md) — Learn to build safely
5. Read: [User Guide → Testing.md](User-Guide/Testing.md) — Learn to test code

### I'm Experienced with Other Frameworks

1. Read: [CLAUDE.md](../CLAUDE.md) — Key architectural decisions
2. Skim: [User Guide → Routing.md](User-Guide/Routing.md) — JSON-based routing is different
3. Skim: [User Guide → Configuration.md](User-Guide/Configuration.md) — Environment-based config with macros
4. Read: [User Guide → Middleware.md](User-Guide/Middleware.md) — Pipeline architecture
5. Reference: [Best Practices/](Best-Practices/) as needed

### I'm Building an API

1. Read: Recipe "Creating Your First API"
2. Read: [User Guide → Routing.md](User-Guide/Routing.md) — Route definition
3. Read: [User Guide → Middleware.md](User-Guide/Middleware.md) — Middleware patterns
4. Read: [User Guide → Security.md](User-Guide/Security.md) — Auth/authorization
5. Read: [Best Practices → API Design](Best-Practices/)
6. Try: Recipes for your specific needs

### I Want to Deploy to Production

1. Read: [User Guide → Configuration.md](User-Guide/Configuration.md) — Environment config
2. Read: [User Guide → Security.md](User-Guide/Security.md) — Security checklist
3. Read: [User Guide → Testing.md](User-Guide/Testing.md) — Test before deploy
4. Try: Recipe "Docker Containerization"
5. Try: Recipe "CI/CD Integration"
6. Monitor: Set up logging and error tracking

### I'm Contributing to SPIN Framework

1. Read: [Contributor-Guide/Development-Setup.md](Contributor-Guide/)
2. Read: [Contributor-Guide/Code-Standards.md](Contributor-Guide/)
3. Read: [Contributor-Guide/Submitting-Changes.md](Contributor-Guide/)
4. Set up your environment
5. Find an issue or feature to work on
6. Submit your pull request

---

## Topic Index

### By Task

**Starting a new project:** Configuration, Routing, Getting Started
**Handling database queries:** Databases, Best Practices → Performance
**Building an API:** Routing, Security, Best Practices → API Design
**Protecting my app:** Security, Best Practices → Security, Middleware
**Improving performance:** Cache, Best Practices → Performance, Recipes → Performance Tuning
**Testing my code:** Testing, Contributor-Guide → Running Tests
**Deploying to production:** Configuration, Security, Recipes → Deployment
**Contributing code:** Contributor-Guide, Testing

### By Feature

**Configuration:** [User-Guide/Configuration.md](User-Guide/Configuration.md), [Best-Practices/](Best-Practices/)
**Routing:** [User-Guide/Routing.md](User-Guide/Routing.md)
**Middleware:** [User-Guide/Middleware.md](User-Guide/Middleware.md), [Best-Practices/](Best-Practices/)
**Databases:** [User-Guide/Databases.md](User-Guide/Databases.md), [Best-Practices/](Best-Practices/), [Recipes/](Recipes/)
**Caching:** [User-Guide/Cache.md](User-Guide/Cache.md), [Best-Practices/](Best-Practices/)
**Security:** [User-Guide/Security.md](User-Guide/Security.md), [Best-Practices/](Best-Practices/)
**Testing:** [User-Guide/Testing.md](User-Guide/Testing.md), [Contributor-Guide/](Contributor-Guide/)
**File Operations:** [User-Guide/Uploaded-files.md](User-Guide/Uploaded-files.md), [User-Guide/Storage-folders.md](User-Guide/Storage-folders.md)

### By Skill Level

**Beginner:** Getting-Started, User-Guide → Configuration, Routing, Middleware
**Intermediate:** User-Guide → Security, Testing, Databases; Best-Practices
**Advanced:** Best-Practices, Recipes, Contributor-Guide

---

## Related Projects

- **[spin-skeleton](https://github.com/Celarius/spin-skeleton)** — Example application demonstrating SPIN Framework usage. Start here to see a working app.
- **[SPIN Framework GitHub](https://github.com/Celarius/spin-framework)** — Source code, issue tracker, releases
- **[Packagist: celarius/spin-framework](https://packagist.org/packages/celarius/spin-framework)** — Installation via Composer

---

## Getting Help

- **Documentation Issues** — Found an error or unclear explanation? [Open an issue](https://github.com/Celarius/spin-framework/issues)
- **Questions** — Check the FAQ in the [Reference/](Reference/) directory
- **Community** — Join our community discussions (link to be added)
- **Security Issues** — Please report responsibly to sandell@celarius.com

---

## Documentation Status

This documentation mirrors the current state of SPIN Framework 0.0.35 (pre-1.0). As the framework evolves, documentation is updated to match. Check the [CHANGELOG](../CHANGELOG.md) for recent changes and the [GitHub releases](https://github.com/Celarius/spin-framework/releases) for version-specific notes.

**Last updated:** March 2026
