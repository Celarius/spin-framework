# Configuration

SPIN Framework uses a JSON-based configuration system that's loaded at runtime and provides easy access through helper functions.

## Configuration Structure

SPIN applications use JSON configuration files organized by environment (e.g., `config-dev.json`, `config-prod.json`). The configuration is structured hierarchically and supports environment variables.

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
    "local.apcu": {
      "adapter": "APCu",
      "class": "\\Spin\\Cache\\Adapters\\Apcu",
      "options": {}
    },
    "remote.redis": {
      "adapter": "Redis",
      "class": "\\Spin\\Cache\\Adapters\\Redis",
      "options": {
        "host": "172.20.0.1",
        "port": 6379
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
  },
  "hooks": [
    {
      "OnBeforeRequest": [
        "\\App\\Hooks\\OnBeforeRequest"
      ],
      "OnAfterRequest": [
        "\\App\\Hooks\\OnAfterRequest"
      ]
    }
  ],
  "connections": {
    "example_mysql": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "<db_schema_name>",
      "host": "localhost",
      "port": 3306,
      "username": "root",
      "password": "*****",
      "charset": "UTF8",
      "options": [
        {
          "ATTR_PERSISTENT": true
        },
        {
          "ATTR_ERRMODE": "ERRMODE_EXCEPTION"
        },
        {
          "ATTR_AUTOCOMMIT": false
        }
      ]
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

SPIN supports environment variables in configuration using `${VARIABLE_NAME}` syntax:

```json
{
  "application": {
    "secret": "${APPLICATION_SECRET}",
    "database": {
      "password": "${DB_PASSWORD}"
    }
  }
}
```

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
    "cookie": "SID",
    "timeout": 3600,
    "refresh": 600,
    "driver": "apcu",
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
  }
}
```

### Cache Configuration

```json
{
  "caches": {
    "local.apcu": {
      "adapter": "APCu",
      "class": "\\Spin\\Cache\\Adapters\\Apcu",
      "options": {}
    },
    "remote.redis": {
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

```json
{
  "connections": {
    "example_mysql": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "<db_schema_name>",
      "host": "localhost",
      "port": 3306,
      "username": "root",
      "password": "*****",
      "charset": "UTF8",
      "options": [
        {
          "ATTR_PERSISTENT": true
        },
        {
          "ATTR_ERRMODE": "ERRMODE_EXCEPTION"
        },
        {
          "ATTR_AUTOCOMMIT": false
        }
      ]
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

### Hooks Configuration

```json
{
  "hooks": [
    {
      "OnBeforeRequest": [
        "\\App\\Hooks\\OnBeforeRequest"
      ],
      "OnAfterRequest": [
        "\\App\\Hooks\\OnAfterRequest"
      ]
    }
  ]
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

## Configuration Caching

SPIN caches configuration in memory for performance. Changes to configuration files require an application restart to take effect.
