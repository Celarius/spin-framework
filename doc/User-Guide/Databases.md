<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent= depth="4" -->

* [1. Databases](#1-databases)
* [1.1 Configuration](#1-configuration)
* [1.2 Driver Names](#1-driver-names)
* [2. Creating custom connections](#2-Creating-custom-connections)


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
      "options": {
        "ATTR_PERSISTENT" : true,
        "ATTR_ERRMODE" : "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT" : false
      } 
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

# 2 Creating custom connections
To create a custom connection on the fly in the code the helper `db()` is used.

Passing the `$name` of the connection as well as the connection `$params` to it. This will create
a new connection in the connecton manager. The `$params` is an array with the same format as the
config parameters.

Example:
```php
# Create a new PdoConnection named `MyCon`
$pdo = db('MyCon', [
  "type"      => "Pdo",
  "driver"    => "{driver_name}",
  "schema"    => "{schema_name}",
  "host"      => "{db_host/ip}",
  "port"      => "{db_port}",
  "username"  => "{db_username}",
  "password"  => "{db_password}",
  "charset"   => "UTF8",
  "options"   => [
    "ATTR_PERSISTENT" => true,
    "ATTR_ERRMODE"    => "ERRMODE_EXCEPTION",
    "ATTR_AUTOCOMMIT" => false
  ]
]);
```
