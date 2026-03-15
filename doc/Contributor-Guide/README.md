# Contributor Guide — SPIN Framework

Welcome to the SPIN Framework Contributor Guide. This comprehensive set of documents helps developers understand the framework architecture, extend it properly, and contribute high-quality changes.

## Quick Navigation

| Document | Purpose | Audience |
|----------|---------|----------|
| [Getting-Started.md](Getting-Started.md) | Development environment setup, contribution workflow | New contributors |
| [Architecture-Overview.md](Architecture-Overview.md) | Core design principles, components, request lifecycle | Framework developers |
| [Extension-Points.md](Extension-Points.md) | Building custom adapters, drivers, middleware, helpers | Extension authors |
| [Testing-Guide.md](Testing-Guide.md) | Test structure, writing tests, coverage requirements | All contributors |
| [Code-Standards.md](Code-Standards.md) | PSR compliance, naming conventions, docblock standards | Code reviewers |
| [Submitting-Changes.md](Submitting-Changes.md) | PR process, changelog, breaking changes, releases | PR submitters |

## Start Here

**First Time Contributing?**
1. Read [Getting-Started.md](Getting-Started.md) to set up your environment
2. Review [Code-Standards.md](Code-Standards.md) for coding requirements
3. Check [Architecture-Overview.md](Architecture-Overview.md) for framework design

**Extending the Framework?**
1. Study [Architecture-Overview.md](Architecture-Overview.md) to understand extension points
2. Follow examples in [Extension-Points.md](Extension-Points.md)
3. Write tests using [Testing-Guide.md](Testing-Guide.md)
4. Submit via [Submitting-Changes.md](Submitting-Changes.md)

**Reviewing Code?**
1. Use the checklist in [Code-Standards.md](Code-Standards.md)
2. Verify tests with [Testing-Guide.md](Testing-Guide.md)
3. Check architecture alignment with [Architecture-Overview.md](Architecture-Overview.md)

## Core Requirements Summary

### Development Setup
- PHP 8.0+
- Composer for dependencies
- PHPUnit for testing
- Git for version control

### Code Quality Standards
- PSR-4 namespace mapping required
- PSR-12 code style (use php-cs-fixer)
- Strict types (`declare(strict_types=1);`)
- All parameters and returns type-hinted
- Minimum 85% test coverage for new features

### Documentation Requirements
- Class docblocks explaining purpose
- Method docblocks with @param/@return
- @throws for exceptions
- Inline comments for complex logic
- CHANGELOG.md updated for all changes

### Testing Requirements
- Unit tests for all new code
- Integration tests for components
- Test success and failure paths
- Coverage reports before merge
- CI/CD passes on all platforms

### Git Workflow
- Feature branches from `develop`
- Atomic, descriptive commits
- Clear pull request descriptions
- Address all review feedback
- No `Co-Authored-By` trailers

## Key Framework Principles

1. **Zero Magic** — Explicit over implicit; no hidden behaviors
2. **JSON-Driven** — Routes and configuration via JSON, not code
3. **PSR Compliant** — Built on established standards (PSR-3, 7, 11, 16, 17)
4. **Lightweight** — Minimal dependencies, essential features only
5. **Extensible** — Clear interfaces for custom implementations
6. **Type-Safe** — Full PHP 8+ type system usage

## Common Tasks

### Running Tests
```bash
# All tests
./vendor/bin/phpunit

# With coverage
./vendor/bin/phpunit --coverage-html coverage/

# Specific test
./vendor/bin/phpunit tests/Core/ControllerTest.php
```

### Code Quality
```bash
# Auto-fix formatting
./vendor/bin/php-cs-fixer fix src/

# Static analysis
./vendor/bin/phpstan analyse src/

# Check coverage
./vendor/bin/phpunit --coverage-text
```

### Creating a Feature
```bash
# Create branch
git checkout -b feature/my-feature develop

# Make changes, add tests
# Verify tests pass and coverage > 85%

# Commit with message
git commit -m "feat: add new feature

- Implementation detail
- Testing approach

Fixes #123"

# Push and create PR
git push origin feature/my-feature
```

## Architecture at a Glance

```
Application (orchestrator)
    ↓
[Global Before Middleware] →
    ↓
[Route Dispatcher] →
    ↓
[Group Before Middleware] →
    ↓
[Route Middleware] →
    ↓
[Controller Handler] (PSR-7 Response)
    ↓
[Group After Middleware] ←
    ↓
[Global After Middleware] ←
    ↓
Response → Client
```

**Key Components:**
- **Application** — Request/response orchestration
- **Controllers** — HTTP method handlers
- **Middleware** — Pipeline interceptors (before/after)
- **Routes** — JSON-based URL mapping
- **Config** — Environment-specific configuration
- **Cache** — Pluggable cache adapters
- **Database** — PDO-based driver abstraction
- **Container** — PSR-11 dependency injection

## Extension Points

Framework is designed for extension:

- **Cache Adapters** — Implement custom storage backends (PSR-16)
- **Database Drivers** — Add database engine support (PDO-based)
- **Middleware** — Create request/response interceptors
- **Helpers** — Add utility functions
- **Services** — Register in container for injection

See [Extension-Points.md](Extension-Points.md) for detailed examples.

## Contributing Guidelines

**Before Starting**
- Check open issues to avoid duplicates
- Discuss major changes in an issue first
- Read relevant documentation

**During Development**
- Add tests alongside code
- Update documentation
- Keep commits atomic
- Follow code standards

**Before Submitting**
- All tests pass
- Coverage >= 85%
- No warnings from phpstan
- Code style checked with php-cs-fixer
- CHANGELOG.md updated

**After Submitting**
- Respond to review feedback
- Push updates (don't force-push)
- Wait for approval
- Maintainer merges to develop, then master

## Resources

- [CHANGELOG.md](../CHANGELOG.md) — Version history and breaking changes
- [Configuration.md](../Configuration.md) — Config file format
- [Routing.md](../Routing.md) — Route definition schema
- [GitHub Issues](https://github.com/celarius/spin-framework/issues) — Bug reports and features
- [GitHub Discussions](https://github.com/celarius/spin-framework/discussions) — Questions and ideas

## Questions?

- Review relevant documentation in this guide
- Check [Getting-Started.md](Getting-Started.md) troubleshooting section
- Open an issue on GitHub
- Ask in pull request comments

## License

SPIN Framework is open source under the MIT License. See LICENSE file for details.

---

**Last Updated:** 2026-03-15
**Framework Version:** 0.0.36+

**Contributing Count:** 6 comprehensive guides covering all aspects of framework development, extension, and contribution.
