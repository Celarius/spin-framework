[![Latest Stable Version](https://poser.pugx.org/celarius/spin-framework/v/stable)](https://packagist.org/packages/celarius/spin-framework)
[![Total Downloads](https://poser.pugx.org/celarius/spin-framework/downloads)](https://packagist.org/packages/celarius/spin-framework)
[![License](https://poser.pugx.org/nofuzz/framework/license)](https://packagist.org/packages/celarius/spin-framework)
[![PHP7 Ready](https://img.shields.io/badge/PHP7-ready-green.svg)](https://packagist.org/packages/celarius/spin-framework)

# Spin - A super lightweight PHP UI/REST framework
[![Latest Unstable Version](https://poser.pugx.org/celarius/spin-framework/v/unstable)](https://packagist.org/packages/celarius/spin-framework)
[![Build Status](https://travis-ci.org/Celarius/spin-framework.svg?branch=master)](https://travis-ci.org/Celarius/spin-framework)

Spin is a application framework for making Web UI's and REST API's quickly and effectively with PHP. It uses [PSR standards](http://www.php-fig.org/psr/)
for most things, and allows for plugging in almost any PSR compatible component, such as loggers, HTTP libraries etc.

```txt
    NOTE: This framework is in ALPHA stage - Not ready for production
```

# Table of Contents
- [1. Features](#1-features)
  - [1.1 PSR based integrations](#1-1-psr-based-integrations)
- [2. Installation](#2-installation)
  - [2.1. Using the spin-skeleton](#2-1-using-the-spin-skeleton)
  - [2.2. Folder structure](#2-2-folder structure)
- [3. Technical Details](#3-technical-details)
  - [3.1. Helper functions](#3-1-helper-functions)
  - [3.2. Request lifecycle](#3-2-request-lifecycle)
- [4. Using Template Engines](#4-using-template-engines)
  - [4.1. Twig](#4-1-twig)
  - [4.2. Plates](#4-2-plates)

# 1. Features
* PHP 7.1+
* Platform agnostic. (Tested: Windows, Linux, Unix)
* Composer driven in all packages/extensions
* Template Engine support (skeleton uses [Plates](http://platesphp.com/) by default)
* PDO based DB connections (MySql,Oracle,CockroachDb,Firebird,Sqlite ...)
* DAO base classes for DB Entity representation

## 1.1. PSR based integrations
* Logger (PSR-3) Defaults to [Monolog](https://github.com/Seldaek/monolog).
* Huggable (PSR-8)
* HTTP Message (PSR-7). Defaults to [Guzzle](https://github.com/guzzle/guzzle)
* Container (PSR-11). Defaults to [The Leauge Container](http://container.thephpleague.com/)
* Events (PSR-14).
* SimpleCache (PSR-16). Defaults to APCu SimpleCache
* HTTP Factories (PSR-17)

# 2. Installation
Installing spin-framework as standalone with composer:
```bash
> composer require celarius/spin-framework
```

## 2.1. Using the spin-skeleton
To install and use the spin-framework it is highly recommended to start by cloning the [spin-skeleton](https://github.com/Celarius/spin-skeleton) and running `composer update -o` in the folder. This will download all needed packages, and create a template skeleton project, containing example configs, routes, controllers and many other things.

# 2.2. folder structure
```txt
/<AppName>
  /src
    /app
      /Config
      /Middlewares
      /Controllers
      /Views
        /Templates
        /Errors
        /Pages
      /Models
      globals.php
    /public
    /storage
      /logs
      /cache
    /vendor
      /celarius/spin-framework
    composer.json
  /tests
```



# 3. Technical Details
## 3.1. Helper functions
The following is a list of helper functions available as global functions.

### 3.1.1. env()
The `env()` function is used to retreive an environment variable from the underlaying OS.
```php
function env(string $var, $default=null)
```
```php
$var = env('ENVIRONMENT');
```

### 3.1.2. app()
The `app()` function is used to retreive the instance of the global application variable. The optional `$property` parameter is used to retreive an application property from the $app object.
```php
function app(string $property=null)
```
```php
$val = app('code');
```

### 3.1.3. config()
The `config()` function is used to retreive the config object, or to get/set a specific config key (or array). If the additional `$value` parameter is given the config `$key` is set to that value.

*Note that the key name is a dot notation. Example: "application.maintenance" gives maintenance key in application array*
```php
function config(string $key=null, string $value=null)
```
```php
$val = config('application.maintenance');
```

### 3.1.4. container()
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

### 3.1.5. logger()
The `logger()` function is used to access the (PSR-3)[http://www.php-fig.org/psr/psr-3/] logger object.
```php
function logger()
```
```php
logger()->critical('Something bad has happened', ['details'=>'Encountered mystic radiation']);
```

### 3.1.6. db()
The `db()` function is used to access one of the defined connections.

*Note: If the $connectionName is not given, the 1st connection in the list of connections is used*
```php
function db(string $connectionName='')
```
```php
$rows = db()->rawQuery('SELECT * FROM table WHERE field = :value',['value'=>123]);
```

### 3.1.7. cache()
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

### 3.1.8. getRequest()
The `getRequest()` function is used to access the (PSR-7)[http://www.php-fig.org/psr/psr-7/] HTTP ServerRequest object

```php
function getRequest()
```
```php
$request = getRequest();
```

### 3.1.9. getResponse()
The `getResponse()` function is used to access the (PSR-7)[http://www.php-fig.org/psr/psr-7/] HTTP Response object

```php
function getResponse()
```
```php
$request = getResponse();
```


## 3.2. Request lifecycle
  1.  Receive request from Client browser to Apache
  2.  Apache loads PHP and runs "bootstrap.php"
  3.  "bootstrap.php" creates $app = new Spin();
      * BOOTSTRAP PHASE:
        - Register Framework Global Helper Functions
        - Load Config
        - Load Factories
          * Cache Factory
          * HTTP Factory
          * Container Factory
          * Event Factory
          * Connections Factory
        - Load Hook Manager
        - Create HTTP Server Request, Response
          * Populate Server Request with data

  4.  "bootstrap.php" code:
        - Register "User" Global Functions        

  5.  "bootstrap.php" calls $app->run();
      * PRE-PROCESS PHASE:
        - Framework Hooks (onBeforeRequest)
          * Load & Create Hooks one by one
          * Foreach Hook call $hook->run(); if == false, terminate
        - PROCESS PHASE:
          * Match Route
            - Execute Global Before Middlewares
            - Execute Route Specific Before Middlewares
            - Load & Call Controller->handle()
            - Execute Route Specific After Middlewares
            - Execute Global After Middlewares
        - POST-PROCESS PHASE:
          * Framework Hooks (onAfterRequest)
            - Load & Create Hooks one by one
            - Foreach Hook call $hook->run(); if == false, terminate

  6.  Send response to Client

# 4. Using Template Engines
## 4.1. Twig

  https://twig.symfony.com/doc/2.x/api.html

```php
  // Twig Loader
  $twigLoader = new Twig_Loader_Filesystem( $app->getAppPath().'/Views/Templates');

  // Set Twig environment options
  $twig = new Twig_Environment($twigLoader, array(
      'cache' => $app->getAppPath().'/storage/cache',
  ));

  // Create pageData array
  $pageData['app'] = $app;
  $pageData['var1'] = "value1";
  $pageData['var2'] = "value2";

  // Load & Render template
  $html = $twig->render('TheFileName.html', $pageData); // loads "/Views/Templates/TheFileName.html"

  // Send response
  response($html);
```

## 4.2. Plates

If the users controller extends from the `AbstractController` in the skeleton then the [Plates](http://platesphp.com/) template engine is initialized
with necessary basics, and enables the user to use it very easily:

```php
  public function handleGET(array $args)
  {
    # Model to send to view
    $model = ['title'=>'PageTitle', 'user'=>'Kim'];

    # Render view
    $html = $this->engine->render('pages::index', $model); // renders /Views/Pages/index.html

    # Send the generated html
    return response($html);
  }

```

