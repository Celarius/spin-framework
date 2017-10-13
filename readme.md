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

# Features
* PHP 7.1+
* Platform agnostic. (Tested: Windows, Linux, Unix)
* Composer driven in all packages/extensions
* Template Engine support (skeleton uses [Plates](http://platesphp.com/) by default)
* PDO based DB connections (MySql,Oracle,CockroachDb,Firebird,Sqlite ...)
* DAO base classes for DB Entity representation

## PSR based integrations
* Logger (PSR-3) Defaults to [Monolog](https://github.com/Seldaek/monolog).
* Huggable (PSR-8)
* HTTP Message (PSR-7). Defaults to [Guzzle](https://github.com/guzzle/guzzle)
* Container (PSR-11). Defaults to [The Leauge Container](http://container.thephpleague.com/)
* Events (PSR-14).
* SimpleCache (PSR-16). Defaults to APCu SimpleCache
* HTTP Factories (PSR-17)

# Installation
Installing spin-framework as standalone with composer:
```bash
> composer require celarius/spin-framework
```

## Using the "spin-skeleton"
To install and use the spin-framework it is highly recommended to start by cloning the [spin-skeleton](https://github.com/Celarius/spin-skeleton) and running `composer update -o` in the folder. This will download all needed packages, and create a template skeleton project, containing example configs, routes, controllers and many other things.


# Request lifecycle

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

# Skeleton application folder structure

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

# Using Template Engines
## Twig

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

## Plates

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

