<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent= depth="4" -->

* [1. Cache](#1-cache)
* [1.1. Instantiating a Cache](#11-instantiating-a-cache)
* [1.2. Configuring a cache](#12-configuring-a-cache)
* [1.3. Using multiple cache adaters](#13-using-multiple-cache-adaters)
* [2. Examples](#2-examples)
* [3. Writing custom adapters](#3-writing-custom-adapters)

<!-- /MarkdownTOC -->

# 1. Databases
Database connections in SPIN are handled via Database Connection Drivers.

The following drivers are available:
- CockroachDB
- Firebird
- MySQL
- ODBC MS SQL Server
- PostgreSQL
- SQLite

## 1.1 Configuration
In the `config-<env>.json` file the database driver is configured in the `connection` section as:
```json
  "connections": {
    "{connection_name}": {
      "type": "Pdo",
      "driver": "{driver_name}",
      "schema": "{schema_name}",
      "host": "{db_host/ip}",
      "port": "{db_port}",
      "username": "{db_username}",
      "password": "{db_password}",
      "charset": "UTF8",
      "options": [
        {"ATTR_PERSISTENT" : true},
        {"ATTR_ERRMODE" : "ERRMODE_EXCEPTION"},
        {"ATTR_AUTOCOMMIT" : false}
      ]
    }
  },
```

## 1.2 Driver names
The different driver names are:
```
CockroachDB         = "cockroachdb"
Firebird            = "firebird"
MySQL               = "mysql"
ODBC MS SQL Server  = "odbc_sqlsrv"
PostgreSQL          = "pgsql"
SQLite              = "sqlite"
```
