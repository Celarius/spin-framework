<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent= depth="4" -->

- [1. Cache](#1-cache)
  - [1.1. Instantiating a Cache](#11-instantiating-a-cache)
  - [1.2. Configuring a cache](#12-configuring-a-cache)
  - [1.3. Using multiple cache adaters](#13-using-multiple-cache-adaters)
- [2. Examples](#2-examples)
  - [2.1 Setting a value](#21-setting-a-value)
  - [2.1 Getting a value](#21-getting-a-value)
- [3. Writing custom adapters](#3-writing-custom-adapters)

<!-- /MarkdownTOC -->

# 1. Cache
Caching can be used in situations where data retreived from other places does not change often, or is slow to fetch.

Examples are _Static lists_ in databases or entire web-pages that are static.

## 1.1. Instantiating a Cache
The cache in SPIN applications is always available through the ```cache()``` static class.

## 1.2. Configuring a cache
The cache is configured in the ```config-<environment>.json``` file under the `caches` key:

```
  "caches": {
    "apcu": {                                   // Name of Cache
      "adapter": "APCu",                        // Adaptername
      "class": "\\Spin\\Cache\\Adapters\\Apcu", // The class to load
      "options": {}                             // Optional options for class
    }
  }
```

## 1.3. Using multiple cache adaters
Using multiple caches is possible through the naming of the cache.

The config would look like this:

```json
  "caches": {
    "apcu": {                                    // Name of Cache
      "adapter": "APCu",                         // Adaptername
      "class": "\\Spin\\Cache\\Adapters\\Apcu",  // The class to load
      "options": {}                              // Optional options for class
    },
    "redis": {                                   // Name of Cache
      "adapter": "Redis",                        // Adaptername
      "class": "\\Spin\\Cache\\Adapters\\Redis", // The class to load
      "options": {}                              // Optional options for class
    }
  }
```

If the code wants to use the "redis" cache then it would be accessed via `cache('redis')`.

# 2. Examples

## 2.1 Setting a value
```php
  # Set cache key 'aKey' = 'value' for 10 seconds
  $ok = cache()->set('aKey', 'value', 10);
```

## 2.1 Getting a value
```php
  # Get cache key 'aKey' into $value
  $value = cache()->get('aKey');

```

# 3. Writing custom adapters
_TBD_
