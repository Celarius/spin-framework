
- [1. Storage folders](#1-storage-folders)
  - [1.1 getStoragePath()](#11-getstoragepath)
  - [1.2 getSharedStoragePath()](#12-getsharedstoragepath)
  - [1.3 Configuration](#13-configuration)

# 1. Storage folders

Two storage paths are available. They serve different purposes and are suited to different use cases.

## 1.1 getStoragePath()

The local storage path is instance-specific. It is intended for temporary files that do not need to survive a restart or be shared with other instances.

```php
$path = app()->getStoragePath();
// e.g. /var/www/myapp/storage
```

The path resolves to `{basePath}/storage` where `basePath` is the root directory passed to the `Application` constructor.

## 1.2 getSharedStoragePath()

The shared storage path is intended for persistent files that must be accessible across all instances of the application (e.g. in a load-balanced or multi-container environment).

```php
$path = app()->getSharedStoragePath();
// e.g. /mnt/shared/dev/myapp
```

The resolved path depends on the `storage.shared` config key:

| `storage.shared` set? | Resolved path |
|-----------------------|---------------|
| Yes | `{storage.shared}/{environment}/{appCode}` |
| No (empty/absent) | Falls back to `getStoragePath()` |

When the resolved path does not exist, the framework attempts to create it automatically. If creation fails, a warning is logged and the path remains as configured.

## 1.3 Configuration

Add a `storage` section to your `config-{env}.json` to enable shared storage:

```json
"storage": {
  "shared": "/mnt/shared"
}
```

With `appCode = myapp` and `environment = prod`, the resolved path becomes `/mnt/shared/prod/myapp`.

The value supports environment variable expansion:

```json
"storage": {
  "shared": "${env:SHARED_STORAGE_PATH}"
}
```

Omit the key (or set it to an empty string) to use local storage only:

```json
"storage": {
  "shared": ""
}
```
