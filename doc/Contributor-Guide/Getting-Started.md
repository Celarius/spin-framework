# Getting Started — SPIN Framework Contributors

Welcome to the SPIN Framework contributor guide. This document covers setting up your development environment and understanding the contribution process.

## Development Environment Setup

### Prerequisites

- PHP 8.0 or higher
- Composer
- Git
- PHPUnit (installed via Composer)
- Optional: Xdebug or PCOV for code coverage
- Optional: Redis for testing Redis cache adapter
- Optional: Docker for isolated database testing

### Initial Setup

```bash
# Clone the repository
git clone https://github.com/celarius/spin-framework.git
cd spin-framework

# Install dependencies
composer install

# Create a feature branch
git checkout -b feature/your-feature-name

# Verify your environment by running tests
./vendor/bin/phpunit
```

### Project Structure Overview

```
spin-framework/
├── src/                 # Framework source code
├── tests/              # Test suite (mirrors src/ structure)
├── doc/                # Documentation
├── config-*.json       # Configuration files
├── composer.json       # Dependency management
└── phpunit.xml.dist    # Test configuration
```

## Running Tests

### Basic Test Execution

```bash
# Linux/macOS
./vendor/bin/phpunit

# Windows
.\phpunit.cmd
```

### Running Specific Tests

```bash
# Run a single test file
./vendor/bin/phpunit tests/Core/ControllerTest.php

# Run a specific test class
./vendor/bin/phpunit --filter ControllerTest

# Run a specific test method
./vendor/bin/phpunit --filter testHandleGetMethod
```

### Code Coverage

```bash
# Generate coverage report (requires Xdebug or PCOV)
./vendor/bin/phpunit --coverage-html coverage/

# Text-based coverage report
./vendor/bin/phpunit --coverage-text

# Coverage threshold validation
./vendor/bin/phpunit --coverage-text --coverage-clover coverage.xml
```

Coverage expectations:
- Minimum 80% for core framework code
- Higher coverage for public APIs and critical paths
- Test new features with >85% coverage

## Git Workflow and Branching

### Branch Naming

- Feature branches: `feature/description`
- Bug fixes: `fix/description`
- Documentation: `docs/description`
- Tests: `test/description`
- Examples: `example/description`

### Commit Messages

Use clear, descriptive commit messages:

```
[type]: Brief description (50 chars max)

Longer explanation if needed, wrapped at 72 characters.
Explain the "why" not just the "what".

Fixes #123
```

Types: `feat`, `fix`, `docs`, `test`, `refactor`, `perf`, `chore`

**Important:** Do NOT include `Co-Authored-By` trailers in commit messages.

### Pull Request Process

1. **Create feature branch** from `develop`
2. **Make changes** and write/update tests
3. **Run full test suite** and ensure all pass
4. **Run coverage** and verify minimum thresholds
5. **Push to GitHub** and create pull request
6. **Add PR description** explaining the change
7. **Request review** from maintainers
8. **Address feedback** and update PR
9. **Squash commits** before merge if requested
10. **Merge to develop**, then maintainers merge to master

### Code Review Process

Your PR will be reviewed for:

- ✓ Alignment with framework design and goals
- ✓ Code quality and adherence to standards (see Code-Standards.md)
- ✓ Test coverage (minimum 80%)
- ✓ Documentation completeness
- ✓ Backward compatibility or proper breaking change documentation
- ✓ PSR compliance
- ✓ Security considerations

Expected timeline: 2-7 days for review feedback

## Contributing Guidelines

### Before Starting

- Check open issues and PRs to avoid duplicates
- Discuss major changes in an issue first
- Review Architecture-Overview.md for framework design
- Read Code-Standards.md for style requirements

### During Development

- Add tests alongside code changes
- Update documentation as you code
- Keep commits atomic and logical
- Reference issue numbers in commits: `Fixes #123`
- Follow PSR-4 namespace mapping rules

### Common Contribution Types

**Bug Fix**
1. Create issue if one doesn't exist
2. Reference issue in branch and commits
3. Add regression test
4. Update CHANGELOG.md

**Feature**
1. Discuss approach in issue or PR description
2. Follow Architecture-Overview.md for extension points
3. Implement with 85%+ test coverage
4. Document public APIs
5. Update CHANGELOG.md with breaking changes section

**Documentation**
1. Verify accuracy against actual code
2. Include examples where applicable
3. Cross-reference related docs
4. Test code examples work

**Performance Improvement**
1. Include benchmarks showing improvement
2. Verify no regressions with full test suite
3. Document any trade-offs made

## Resources

- [Architecture Overview](Architecture-Overview.md) — Core design and components
- [Code Standards](Code-Standards.md) — Style and quality requirements
- [Extension Points](Extension-Points.md) — How to extend the framework
- [Testing Guide](Testing-Guide.md) — Writing tests for changes
- [Submitting Changes](Submitting-Changes.md) — PR and release process
- [CHANGELOG.md](../CHANGELOG.md) — Version history and breaking changes

## Questions or Issues?

- Open an issue on GitHub
- Check existing documentation
- Review related code examples in tests/
- Ask in PR comments or discussions

---

**Last Updated:** 2026-03-15
**Framework Version:** 0.0.36+
