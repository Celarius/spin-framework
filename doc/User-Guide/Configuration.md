# Configuration

SPIN Framework uses a JSON-based configuration system that's loaded at runtime and provides easy access through helper functions.

## Configuration Structure

SPIN applications use JSON configuration files organized by environment (e.g., `config-dev.json`, `config-prod.json`). The configuration is structured hierarchically and supports environment variables.

_Configuration files support `${env:<varName>}` macros for environment variables in values_

### Basic Configuration File Structure

```json
{
  "application": {
    "global": {
      "maintenance": false,
      "message": "We are in maintenance mode, back shortly",
      "timezone": "Europe/Stockholm"
    },
    "secret": "${application-secret}"
  },

  "session": {
    "cookie": "SID",
    "timeout": 3600,
    "refresh": 600,
    "driver": "apcu",
    "apcu": {
      "option": "value"
    }
  },

  "logger": {
    "level": "notice",
    "driver": "php",
    "drivers": {
      "php": {
        "line_format": "[%channel%] [%level_name%] %message% %context%",
        "line_datetime": "Y-m-d H:i:s.v e"
      },
      "file": {
        "file_path": "storage/log",
        "file_format": "Y-m-d",
        "line_format": "[%datetime%] [%channel%] [%level_name%] %message% %context%",
        "line_datetime": "Y-m-d H:i:s.v e"
      }
    }
  },

  "templates": {
    "extension": "html",
    "errors": "/Views/Errors",
    "pages": "/Views/Pages"
  },

  "caches": {
    "apcu": {
      "adapter": "APCu",
      "class": "\\Spin\\Cache\\Adapters\\Apcu",
      "options": {}
    },
    "redis": {
      "adapter": "Redis",
      "class": "\\Spin\\Cache\\Adapters\\Redis",
      "options": {
        "host": "172.20.0.1",
        "port": 6379
      }
    }
  },

  "connections": {
    "example_mysql": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "${env:DB_DATABASE}",
      "host": "${env:DB_HOST}",
      "port": "${env:DB_PORT}",
      "username": "${env:DB_USER}",
      "password": "${env:DB_PASS}",
      "charset": "UTF8",
      "options": {
        "ATTR_PERSISTENT": false,
        "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT": false,
        "ATTR_EMULATE_PREPARES": false
      }
    }
  },

  "factories": {
    "http": {
      "serverRequest": {
        "class": "\\Spin\\Factories\\Http\\ServerRequestFactory",
        "options": {}
      },
      "request": {
        "class": "\\Spin\\Factories\\Http\\RequestFactory",
        "options": {}
      },
      "response": {
        "class": "\\Spin\\Factories\\Http\\ResponseFactory",
        "options": {}
      },
      "stream": {
        "class": "\\Spin\\Factories\\Http\\StreamFactory",
        "options": {}
      },
      "uploadedFile": {
        "class": "\\Spin\\Factories\\Http\\UploadedFileFactory",
        "options": {}
      },
      "uri": {
        "class": "\\Spin\\Factories\\Http\\UriFactory",
        "options": {}
      }
    },
    "container": {
      "class": "\\Spin\\Factories\\ContainerFactory",
      "options": {
        "autowire": true
      }
    },
    "event": {
      "class": "\\Spin\\Factories\\EventFactory",
      "options": {}
    }
  }
}
```

## Accessing Configuration

SPIN provides a `config()` helper function to access configuration values using dot notation:

```php
// Access nested configuration values
$maintenance = config('application.global.maintenance');
$timezone = config('application.global.timezone');
$sessionTimeout = config('session.timeout');

// Access with default values
$logLevel = config('logger.level', 'info');
$dbHost = config('connections.example_mysql.host', 'localhost');
```

## Environment Variables

### `.env` File Support

SPIN automatically loads a `.env` file from the project root at startup, before any
configuration is processed. Variables defined there become available to `${env:VAR}`
macros and the `env()` helper function.

```
# .env (project root — never commit to version control)
DB_HOST=localhost
DB_USER=myuser
DB_PASS=s3cr3t
APP_ENV=dev
```

**Priority:** Real environment variables (OS, Docker, CI) always win. `.env` values are
only applied when the variable is not already set in the process environment. This means
container-injected or CI secrets are never overridden by a local `.env` file.

### `${env:VAR}` Macro Syntax

SPIN supports environment variable substitution in JSON config values using `${env:VARIABLE_NAME}` syntax:

```json
{
  "application": {
    "secret": "${env:APPLICATION_SECRET}",
    "database": {
      "password": "${env:DB_PASS}"
    }
  }
}
```

### Inline Defaults

Macros support a fallback value used when the variable is not set:

```json
{
  "database": {
    "driver":   "${env:DB_DRIVER:pdo}",
    "host":     "${env:DB_HOST:localhost}",
    "port":     "${env:DB_PORT:3306}"
  }
}
```

If `DB_DRIVER` is not in the environment (and not in `.env`), the value resolves to `pdo`.
Missing variables with no inline default resolve to an empty string.

## Configuration Sections

### Application Configuration

```json
{
  "application": {
    "global": {
      "maintenance": false,
      "message": "We are in maintenance mode, back shortly",
      "timezone": "Europe/Stockholm"
    },
    "secret": "${application-secret}"
  }
}
```

### Session Configuration

```json
{
  "session": {
    "cookie": "SID",      // Cookie name
    "timeout": 3600,      // Timeout in seconds
    "refresh": 600,
    "driver": "apcu",     // Driver name
    "apcu": {
      "option": "value"
    }
  }
}
```

### Logger Configuration

```json
{
  "logger": {
    "level": "notice",      // Log level
    "driver": "php",        // Driver name
    "drivers": {
      "php": {
        "line_format": "[%channel%] [%level_name%] %message% %context%",
        "line_datetime": "Y-m-d H:i:s.v e"
      },
      "file": {
        "file_path": "storage/log",
        "file_format": "Y-m-d",
        "line_format": "[%datetime%] [%channel%] [%level_name%] %message% %context%",
        "line_datetime": "Y-m-d H:i:s.v e"
      }
    }
  }
}
```

### Cache Configuration

```json
{
  "caches": {
    "apcu": {             // WebServer in-memory cache
      "adapter": "APCu",
      "class": "\\Spin\\Cache\\Adapters\\Apcu",
      "options": {}
    },
    "redis": {
      "adapter": "Redis",
      "class": "\\Spin\\Cache\\Adapters\\Redis",
      "options": {
        "host": "172.20.0.1",
        "port": 6379
      }
    }
  }
}
```

### Database Connections

The 1st connection in the `"connections"` array is the **default**, and does not need to be named when using `db()` funtions.

```json
{
  "connections": {
    "example_mysql": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "${env:DB_DATABASE}",
      "host": "${env:DB_HOST}",
      "port": "${env:DB_PORT}",
      "username": "${env:DB_USER}",
      "password": "${env:DB_PASS}",
      "charset": "UTF8",
      "options": {
        "ATTR_PERSISTENT": false,
        "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT": false,
        "ATTR_EMULATE_PREPARES": false
      }
    },
    "example_sqlite": {
      "type": "Pdo",
      "driver": "SqlLite",
      "filename": "storage\\database\\db.sqlite"
    }
  }
}
```

### Factory Configuration

```json
{
  "factories": {
    "http": {
      "serverRequest": {
        "class": "\\Spin\\Factories\\Http\\ServerRequestFactory",
        "options": {}
      },
      "response": {
        "class": "\\Spin\\Factories\\Http\\ResponseFactory",
        "options": {}
      }
    },
    "container": {
      "class": "\\Spin\\Factories\\ContainerFactory",
      "options": {
        "autowire": true
      }
    }
  }
}
```

## Configuration Best Practices

1. **Environment Separation**: Use different configuration files for different environments (dev, staging, prod)
2. **Sensitive Data**: Store sensitive information like passwords and secrets in environment variables
3. **Validation**: Validate configuration values at startup
4. **Defaults**: Provide sensible default values for optional configuration
5. **Documentation**: Document all configuration options and their expected values

## Configuration Validation

SPIN automatically validates configuration when the application starts. Missing required configuration will cause the application to fail to start.

## Dynamic Configuration

While SPIN primarily uses static JSON configuration, you can also set configuration values programmatically:

```php
// Set configuration values at runtime
config('application.global.maintenance', true);
config('session.timeout', 7200);
```
