# Submitting Changes — SPIN Framework

This document covers the process for submitting changes to the SPIN Framework, from preparation through release.

## Pull Request Process

### Step 1: Prepare Your Changes

Before creating a pull request, ensure:

```bash
# Create feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/my-feature

# Make your changes and test
./vendor/bin/phpunit
./vendor/bin/phpunit --coverage-text

# Verify code quality
./vendor/bin/php-cs-fixer fix src/
./vendor/bin/phpstan analyse src/
```

### Step 2: Commit with Clear Messages

Write atomic, descriptive commits:

```bash
# Single commit for small changes
git commit -m "feat: add user authentication middleware

- Implement JWT token validation
- Add token refresh endpoint
- Include comprehensive test coverage"

# Multiple commits for larger features
git commit -m "feat: add caching layer to database queries"
git commit -m "test: add cache adapter test suite"
git commit -m "docs: document cache configuration"
```

**Commit Message Format:**

```
[type]: Brief description (50 chars max)

Longer explanation of changes, wrapped at 72 characters.
Explain the "why", not just the "what". Link issues:

Fixes #123
Related to #456
```

**Type prefixes:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation
- `test:` Tests only
- `refactor:` Code reorganization
- `perf:` Performance improvement
- `chore:` Maintenance tasks

**Important:** Do NOT include `Co-Authored-By` trailers in commit messages.

### Step 3: Push and Create Pull Request

```bash
# Push branch to GitHub
git push origin feature/my-feature

# GitHub will show option to create pull request
# Click "Compare & pull request"
```

### Step 4: PR Description Template

Use this template for comprehensive PR descriptions:

```markdown
## Description
Brief summary of changes and why they're needed.

## Type of Change
- [x] Bug fix (fixes #123)
- [ ] New feature (resolves #456)
- [ ] Breaking change (major version bump required)
- [ ] Documentation update

## Testing
- [x] Added tests for new functionality
- [x] All tests pass locally
- [x] Coverage is 85%+ (show: `./vendor/bin/phpunit --coverage-text`)

## Code Quality
- [x] Code follows PSR-12 style (run: `php-cs-fixer fix src/`)
- [x] Type hints added to all parameters and returns
- [x] Docblocks complete for public methods
- [x] No debug code or commented-out sections

## Breaking Changes
None. All changes are backward compatible.

(OR)

This is a **BREAKING CHANGE**:
- Changed Controller method signatures
- Updated cache adapter interface
- [See Migration Guide below]

## Checklist
- [x] Feature complete and tested
- [x] Documentation updated (if applicable)
- [x] CHANGELOG.md updated
- [x] No new dependencies added
- [x] Ready for review

## Related Issues
Fixes #123
Related to #456
```

### Step 5: Respond to Review Feedback

Reviews will check:

**Architecture**
- Alignment with framework design
- Extension point usage
- PSR compliance
- Security implications

**Code Quality**
- Type safety and hints
- Docblock completeness
- Naming conventions
- Test coverage

**Testing**
- Minimum 85% coverage
- Edge cases covered
- Error handling verified
- Integration tested

**Documentation**
- API documentation clear
- Usage examples provided
- Configuration documented
- Changelog updated

**Response Guidelines:**
- Address all feedback or explain disagreement
- Update code and push new commits
- Don't force-push (maintain commit history)
- Request re-review after changes

### Step 6: Approval and Merge

Once approved:

1. **Squash commits** if requested (multiple commits combined)
2. **Merge to develop** via GitHub
3. **Delete feature branch**
4. **Maintainer merges develop → master** periodically

## Changelog Updates

Update `CHANGELOG.md` for all changes (except documentation-only):

### Location

File: `/CHANGELOG.md`

### Format

```markdown
## [Unreleased]

### Added
- New user authentication middleware
- Support for Redis cache adapter
- Database query caching layer

### Changed
- Updated Config class for better performance
- Refactored middleware pipeline for clarity

### Fixed
- Fixed issue with DateInterval in APCu adapter
- Corrected JWT token validation edge case

### Deprecated
- Logger::log() method (use Logger::info() instead)

### Breaking Changes
- Controller::handleRequest() method signature changed
- Cache adapter interface updated

### Security
- Fixed SQL injection vulnerability in QueryBuilder
```

### Guidelines

- **Added** — New features only
- **Changed** — Modifications to existing features
- **Fixed** — Bug fixes
- **Deprecated** — Features marked for removal
- **Breaking Changes** — Incompatible changes (major version)
- **Security** — Security-related fixes (always highlight)

**Example entry:**

```markdown
### Added
- Cache adapter for Memcached support (#456)
- New `cache()->has()` method for checking key existence
```

## Documentation Updates

Always update relevant documentation:

### When to Update Docs

| Change | Document |
|--------|----------|
| New cache adapter | Extension-Points.md, config example |
| New middleware | Extension-Points.md, usage example |
| API change | Relevant feature doc in `/doc/` |
| Configuration option | Configuration.md |
| Breaking change | CHANGELOG.md Migration Guide section |

### Example: Adding a Feature to Docs

If adding a new cache adapter:

1. **Update Extension-Points.md** with example implementation
2. **Add configuration example** in the adapter section
3. **Include test example** showing usage
4. **Update CHANGELOG.md** with "Added" section
5. **Link from relevant feature docs**

## Breaking Change Documentation

Breaking changes require comprehensive documentation:

### In CHANGELOG.md

```markdown
## [2.0.0] - 2026-04-15

### BREAKING CHANGES

#### Controller Method Signature
**Old:**
```php
public function handleGET($args)
```

**New:**
```php
public function handleGET(array $args): ResponseInterface
```

**Migration:**
- Add type hints to all controller methods
- Ensure all handlers return ResponseInterface
- Test with new framework version

**PR:** #789
**Migration Guide:** See [UPGRADE-2.0.md](docs/UPGRADE-2.0.md)

#### Cache Adapter Interface
- New required method: `getMultiple()`
- Removed deprecated method: `fetch()`

**Migration:**
All custom cache adapters must implement new interface.
```

### Create Migration Guide

For major breaking changes, create `docs/UPGRADE-X.0.md`:

```markdown
# Upgrading to SPIN Framework 2.0

## Overview
Major version 2.0 includes significant architectural improvements.
Most applications will upgrade seamlessly. This guide covers breaking changes.

## Breaking Changes

### 1. Controller Method Signatures

**Before (1.x):**
```php
public function handleGET($args) { }
```

**After (2.0):**
```php
public function handleGET(array $args): ResponseInterface { }
```

**Action Required:**
1. Add `array` type hint to all methods
2. Add `ResponseInterface` return type
3. Ensure methods return response objects

### 2. Cache Adapter Interface

New methods required:
- `getMultiple(iterable $keys, $default = null): iterable`
- `setMultiple(iterable $values, $ttl = null): bool`

**Action Required:**
1. Update custom adapters
2. Implement new methods
3. Run test suite

## New Features

- Built-in middleware caching
- Async database queries
- Auto-configuration from environment

## Questions?
[Open an issue on GitHub](https://github.com/celarius/spin-framework/issues)
```

## Testing Your Changes Before Submitting

### Full Test Suite

```bash
# Run all tests
./vendor/bin/phpunit

# With coverage report
./vendor/bin/phpunit --coverage-html coverage/

# Stop on first failure (faster debugging)
./vendor/bin/phpunit --stop-on-failure

# Verbose output
./vendor/bin/phpunit -v
```

### Code Quality Checks

```bash
# Code style formatting
./vendor/bin/php-cs-fixer fix src/ --dry-run

# Static analysis
./vendor/bin/phpstan analyse src/

# Find potential issues
./vendor/bin/psalm
```

### Manual Testing

```bash
# Test the skeleton project
cd ../spin-skeleton
composer update
./vendor/bin/phpunit
```

## Release Process

### Timeline

Releases follow semantic versioning: `MAJOR.MINOR.PATCH`

- **Patch releases** (e.g., 0.0.37) — Bug fixes only
- **Minor releases** (e.g., 0.1.0) — New features, backward compatible
- **Major releases** (e.g., 1.0.0) — Breaking changes allowed

### Release Steps (Maintainer)

1. **Finalize CHANGELOG.md**
   - Move [Unreleased] section to version number
   - Add release date

2. **Update version in files**
   ```php
   // src/Application.php
   const VERSION = '0.0.37';

   // composer.json
   "version": "0.0.37"
   ```

3. **Create release commit**
   ```bash
   git checkout master
   git pull origin master
   git commit -am "release: version 0.0.37"
   ```

4. **Tag release**
   ```bash
   git tag -a v0.0.37 -m "Release version 0.0.37"
   git push origin master --tags
   ```

5. **Create GitHub release**
   - Go to Releases page
   - Create new release from tag
   - Copy CHANGELOG section as release notes
   - Publish release

6. **Update skeleton project**
   ```bash
   cd ../spin-skeleton
   composer require celarius/spin-framework:^0.0.37
   git commit -am "chore: update spin-framework to 0.0.37"
   git push
   ```

### Release Announcement

Post announcement including:
- Version number and release date
- Key features and fixes
- Breaking changes (if any)
- Link to full CHANGELOG
- Migration guide (if needed)

## Version Support Policy

| Version | Status | Support Until |
|---------|--------|---------------|
| 0.0.x   | Pre-release | Active development |
| 1.0.x   | Current | 2 years from release |
| 0.9.x   | Deprecated | 6 months from 1.0 |

Pre-1.0 releases may have breaking changes in minor versions.

## Common PR Issues and Fixes

### Merge Conflicts

```bash
# Update branch with latest develop
git fetch origin
git rebase origin/develop

# Resolve conflicts, then push
git add .
git rebase --continue
git push origin feature/my-feature --force
```

### Failing Tests

```bash
# Run tests locally first
./vendor/bin/phpunit

# Debug specific test
./vendor/bin/phpunit tests/MyTestFile.php --filter testMethodName -v

# Check coverage
./vendor/bin/phpunit --coverage-text | grep -A 5 "not covered"
```

### Code Style Issues

```bash
# Auto-fix formatting
./vendor/bin/php-cs-fixer fix src/

# Run analysis
./vendor/bin/phpstan analyse src/

# Push fixed code
git add src/
git commit -m "style: apply code formatting"
git push
```

## Resources

- [Getting Started](Getting-Started.md) — Development setup
- [Code Standards](Code-Standards.md) — Coding requirements
- [Testing Guide](Testing-Guide.md) — Test expectations
- [Architecture Overview](Architecture-Overview.md) — Design principles
- [CHANGELOG.md](../CHANGELOG.md) — Version history

---

**Last Updated:** 2026-03-15
**Framework Version:** 0.0.36+
