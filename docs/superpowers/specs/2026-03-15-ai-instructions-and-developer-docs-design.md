# Design: AI Instructions and Comprehensive Developer Documentation

**Date:** 2026-03-15
**Scope:** Multi-system AI instructions + reorganized developer documentation
**Status:** Approved for implementation

---

## 1. Overview

Create AI instructions for Claude, GitHub Copilot, and generic LLMs, complemented by comprehensive developer documentation that serves both framework users (building apps with SPIN) and contributors (extending the framework).

---

## 2. AI Instructions for Multiple Systems

### 2.1 Claude Instructions (`.claude/claude-instructions.md`)

**Purpose:** Guide Claude (Claude Code, Claude API, Claude web) in understanding and working with the SPIN Framework.

**Content Structure:**
- Framework overview and philosophy
- Project structure and naming conventions
- Key architectural patterns (JSON routing, middleware pipeline, PSR compliance)
- How to extend: controllers, middleware, cache adapters, database drivers
- Testing patterns and conventions
- Common tasks and solutions
- When to ask for user clarification
- File naming and code organization rules
- Best practices for generating SPIN code

**Audience:** Claude instances used for development, debugging, and feature work on SPIN apps and the framework itself.

---

### 2.2 GitHub Copilot Instructions (`.github/copilot-instructions.md`)

**Purpose:** Guide GitHub Copilot in suggesting appropriate SPIN-compliant code in the IDE.

**Content Structure:**
- Project structure overview
- Naming conventions and file organization
- Code style guidelines (PSR-12, strict types, type hints)
- Common patterns to suggest (controller handlers, middleware methods, routes)
- Framework dependencies and libraries to reference
- Anti-patterns to avoid
- Testing patterns

**Audience:** GitHub Copilot running in IDEs during development.

---

### 2.3 Generic LLM Instructions (`.claude/llm-instructions.md`)

**Purpose:** Enable other LLMs (ChatGPT, generic Claude API usage, Gemini, etc.) to understand and work with SPIN Framework.

**Content Structure:**
- Framework essentials (what is SPIN, core concepts)
- Architecture overview
- Key files and their purposes
- Coding standards and conventions
- How to extend the framework
- Common pitfalls and how to avoid them
- References to detailed documentation

**Audience:** Other LLMs used for learning, generating code, or solving problems related to SPIN.

---

## 3. Developer Documentation Reorganization

### 3.1 Directory Structure

```
doc/
├── README.md                          # Navigation hub for all docs
├── Getting-Started/                   # User guide section
│   ├── Quick-Start.md
│   ├── Project-Structure.md
│   ├── Core-Concepts.md
│   └── Your-First-App.md
├── User-Guide/                        # Building apps with SPIN
│   ├── Configuration.md               # (existing, moved)
│   ├── Routing.md                     # (existing, moved)
│   ├── Middleware.md                  # (existing, moved)
│   ├── Controllers.md                 # (new, expanded from README)
│   ├── Databases.md                   # (existing, moved)
│   ├── Cache.md                       # (existing, moved)
│   ├── Uploaded-files.md              # (existing, moved)
│   ├── Storage-folders.md             # (existing, moved)
│   ├── Helpers.md                     # (existing, moved)
│   └── Security.md                    # (existing, moved)
├── Best-Practices/                    # Patterns and approaches
│   ├── Application-Design.md          # (new)
│   ├── Error-Handling.md              # (new)
│   ├── Performance-Optimization.md    # (new)
│   ├── Database-Patterns.md           # (new)
│   ├── Caching-Strategies.md          # (new)
│   └── Testing-Patterns.md            # (new)
├── Recipes/                           # Common tasks and solutions
│   ├── Authentication.md              # (new)
│   ├── File-Uploads.md                # (new)
│   ├── Rate-Limiting.md               # (new)
│   ├── CORS-Handling.md               # (new)
│   ├── API-Versioning.md              # (new)
│   └── Deployment.md                  # (new)
├── Contributor-Guide/                 # Contributing to SPIN framework
│   ├── Getting-Started.md             # (new)
│   ├── Architecture-Overview.md       # (new)
│   ├── Extension-Points.md            # (new)
│   ├── Testing-Guide.md               # (new)
│   ├── Code-Standards.md              # (new)
│   └── Submitting-Changes.md          # (new)
└── Reference/                         # Technical reference
    └── API-Reference.md               # (new, auto-generated or curated)
```

### 3.2 Doc Navigation (doc/README.md)

Create a comprehensive navigation document that:
- Explains what each section is for
- Provides quick links to common tasks
- Shows recommended reading order for new users
- Distinguishes between user guide and contributor guide
- Includes search/topic index

### 3.3 Key New Guides

**Getting Started Section:**
- **Quick-Start.md** — 5-minute setup and first route
- **Project-Structure.md** — Understanding the app layout and framework files
- **Core-Concepts.md** — JSON routing, middleware pipeline, PSR compliance, global helpers
- **Your-First-App.md** — Step-by-step tutorial building a basic CRUD app

**Best Practices Section:**
- **Application-Design.md** — Structuring apps for maintainability, separation of concerns
- **Error-Handling.md** — Exception handling patterns, custom exceptions, error responses
- **Performance-Optimization.md** — Caching strategies, query optimization, middleware efficiency
- **Database-Patterns.md** — Connection management, query patterns, transactions
- **Caching-Strategies.md** — When to cache, cache invalidation, adapter selection
- **Testing-Patterns.md** — Unit tests, integration tests, mocking strategies (mirrors Testing.md)

**Recipes Section:**
- **Authentication.md** — JWT, session-based, middleware patterns
- **File-Uploads.md** — Validation, security, storage integration (expands Uploaded-files.md)
- **Rate-Limiting.md** — Implementing request rate limiting, throttling
- **CORS-Handling.md** — Cross-origin requests, preflight handling, CORS middleware
- **API-Versioning.md** — Versioning strategies, backward compatibility, route groups
- **Deployment.md** — Environment configuration, database migrations, health checks

**Contributor Guide Section:**
- **Getting-Started.md** — Setting up dev environment, running tests, code review process
- **Architecture-Overview.md** — Core components, request lifecycle, DI container usage
- **Extension-Points.md** — Creating cache adapters, database drivers, custom middleware
- **Testing-Guide.md** — Writing tests for framework changes, test organization, CI/CD
- **Code-Standards.md** — PSR compliance, type hints, naming conventions, docblock standards
- **Submitting-Changes.md** — PR process, changelog updates, breaking change documentation

---

## 4. Implementation Details

### 4.1 File Handling

**New Files Created:**
- `.claude/claude-instructions.md` — ~2,000 words
- `.github/copilot-instructions.md` — ~1,500 words
- `.claude/llm-instructions.md` — ~1,500 words
- `doc/README.md` — ~1,000 words (navigation hub)
- `doc/Getting-Started/*` — 4 guides, ~300-400 words each
- `doc/Best-Practices/*` — 6 guides, ~400-600 words each
- `doc/Recipes/*` — 6 guides, ~400-600 words each
- `doc/Contributor-Guide/*` — 6 guides, ~300-500 words each
- `doc/Reference/API-Reference.md` — ~1,500-2,000 words (curated from src/ code)

**Existing Files:**
- Moved from `doc/` to appropriate sections (no content change)
- Updated cross-references in moved files

**Updated Files:**
- `CLAUDE.md` — Add reference to new `.claude/claude-instructions.md`
- `README.md` — Add link to comprehensive `doc/` structure

### 4.2 Content Principles

**For All AI Instructions:**
- Framework-first perspective (how does SPIN do this)
- Emphasis on JSON routing, middleware pipeline, PSR compliance
- Concrete code examples
- Anti-patterns to avoid
- Clear extension points

**For Getting Started Docs:**
- Assume basic PHP knowledge, new to SPIN
- Progressive complexity
- Hands-on tutorials with runnable examples
- Clear learning path

**For Best Practices Docs:**
- Assume SPIN basics understood
- Focus on "why" and "when," not just "what"
- Real-world scenarios
- Trade-offs and considerations
- Links to related docs

**For Recipes:**
- Problem-solution format
- Step-by-step implementation
- Code snippets (runnable, tested)
- Links to related best practices

**For Contributor Guide:**
- Architecture first, then implementation
- Clear extension interfaces
- Testing requirements
- Review checklist

---

## 5. Success Criteria

✅ All AI instructions are discoverable from their expected locations
✅ Framework patterns are clearly explained with examples
✅ New developers can follow Getting Started → Core Concepts → Your First App
✅ Experienced developers find Best Practices and Recipes for common tasks
✅ Contributors understand how to extend the framework via extension guides
✅ All guides cross-reference related topics
✅ Navigation hub (doc/README.md) helps users find what they need
✅ Code examples are tested and runnable

---

## 6. Next Steps

1. Write design document (this file) ✅
2. Invoke writing-plans skill for implementation planning
3. Create AI instruction files with system-specific guidance
4. Create Getting Started guides and onboarding path
5. Create Best Practices guides and recipe collection
6. Create Contributor Guide for framework developers
7. Reorganize existing docs and add cross-references
8. Update CLAUDE.md and README.md with references to new structure
9. Test all code examples and verify links
10. Commit and create PR
