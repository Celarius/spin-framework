# Table of contents
<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent="" -->

* [1. Helpers](#1-helpers)
* [1.1. env\(\)](#11-env)
* [1.2. app\(\)](#12-app)
* [1.3. config\(\)](#13-config)
* [1.4. container\(\)](#14-container)
* [1.5. logger\(\)](#15-logger)
* [1.6. db\(\)](#16-db)
* [1.7. cache\(\)](#17-cache)
* [1.8. getRequest\(\)](#18-getrequest)
* [1.9. getResponse\(\)](#19-getresponse)
* [1.10. queryParams\(\)](#110-queryparams)
* [1.11. queryParam\(\)](#111-queryparam)
* [1.12. postParams\(\)](#112-postparams)
* [1.13. postParam\(\)](#113-postparam)

<!-- /MarkdownTOC -->

# 1. Helpers
The following is a list of helper functions available as global functions.

## 1.1. env()
The `env()` function is used to retreive an environment variable from the underlaying OS.
```php
function env(string $var, $default=null)
```
```php
$var = env('ENVIRONMENT');
```

## 1.2. app()
The `app()` function is used to retreive the instance of the global application variable. The optional `$property` parameter is used to retreive an application property from the $app object.
```php
function app(string $property=null)
```
```php
$val = app('code');
```

## 1.3. config()
The `config()` function is used to retreive the config object, or to get/set a specific config key (or array). If the additional `$value` parameter is given the config `$key` is set to that value.

*Note that the key name is a dot notation. Example: "application.maintenance" gives maintenance key in application array*
```php
function config(string $key=null, string $value=null)
```
```php
$val = config('application.maintenance');
```

## 1.4. container()
The `container()` function is used to retreive the container object, or to get/set a specific container key. 

If the additional `$id` parameter is given the container item with `$key` is returned.

If the additional `$value` parameter is given the container item with `$key` is set to the `$value`. This can be any valid variable, even callables.
```php
function container(string $id=null, $value=null)
```
```php
container('MySuperFunc', function() {
  // do something in the Super Function
}
);

# Call the defined callable
container('MySuperFunc');
```

## 1.5. logger()
The `logger()` function is used to access the (PSR-3)[http://www.php-fig.org/psr/psr-3/] logger object.
```php
function logger()
```
```php
logger()->critical('Something bad has happened', ['details'=>'Encountered mystic radiation']);
```

## 1.6. db()
The `db()` function is used to access one of the defined connections.

*Note: If the $connectionName is not given, the 1st connection in the list of connections is used*
```php
function db(string $connectionName='')
```
```php
$rows = db()->rawQuery('SELECT * FROM table WHERE field = :value',['value'=>123]);
```

## 1.7. cache()
The `cache()` function is used to access one of the defined caches. The caches are shared between connections, regardless of the driver.

*Note: If the $driverName is not given, the 1st connection in the list of connections is used*
```php
function cache(string $driverName='')
```
```php
# Set a value
cache('APCU')->set('key1',1234);

# Get a value
$value = cache('APCU')->get('key1');
```

## 1.8. getRequest()
The `getRequest()` function is used to access the (PSR-7)[http://www.php-fig.org/psr/psr-7/] HTTP ServerRequest object

```php
function getRequest()
```
```php
$request = getRequest();
```

## 1.9. getResponse()
The `getResponse()` function is used to access the (PSR-7)[http://www.php-fig.org/psr/psr-7/] HTTP Response object

```php
function getResponse()
```
```php
$request = getResponse();
```

## 1.10. queryParams()
The `queryParams()` function returns an array with all query parameters (on the URL).

```php
function queryParams()
```
```php
$params = queryParams();
$value =$params['xyz'] ?? '';
```

## 1.11. queryParam()
The `queryParam()` function returns all one named query parameter, or null.

```php
function queryParam(string $paramName, $default=null)
```
```php
$value = queryParam('xyz') ?? '';
```

## 1.12. postParams()
The `postParams()` function returns an array with all post parameters. The post params are decoded if they are sent in a post request with the content-type `multipart/form-data`.

```php
function postParams()
```
```php
$params = postParams();
$value =$params['xyz'] ?? '';
```

## 1.13. postParam()
The `postParam()` function returns all one named post parameter, or null.

```php
function postParam(string $paramName, $default=null)
```
```php
$value = postParam('xyz') ?? '';
```
