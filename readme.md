# Spin - A PHP UI/REST framework

Spin is a application framework for making Web UI's and REST API's quickly and effectively with PHP. It uses [PSR standards](http://www.php-fig.org/psr/)
for most things, and allows for plugging in almost any PSR compatible component, such as loggers, HTTP libraries etc.

# Features
* PHP 7.1+
* Composer driven in all components
* Controller->Model->View support (template engines, [Plates](http://platesphp.com/) by default)
* PDO based DB connections
* DAO classes for DB Entity representation

## PSR based integrations
* Logger (PSR-3) Defaults to Monolog.
* Huggable (PSR-8)
* HTTP Message (PSR-7). Defaults to Guzzle
* Container (PSR-11). Defaults to The Leauge Container
* Events (PSR-14).
* SimpleCache (PSR-16). Defaults to APCu SimpleCache (in memory)
* HTTP Factories (PSR-17)

# Request lifecycle

  1.  Receive request from Client browser to Apache
  2.  Apache loads "bootstrap.php" file and starts PHP processing
  3.  Bootstrap initializes Framework, and starts to process the request
      - Create $app class (PSR-8 :: extends \Psr\Hug\Huggable)
        BOOTSTRAP PHASE:
          - Register Framework Global Helper Functions
          - Load Config

          - Load Factories
            - Cache Factory        - Cache Manager (PSR-6 / PSR-16)
            - HTTP Factory         - HTTP Manager (PSR-17 :: guzzle)
            - Container Factory    - Container Manager (PSR-11 :: http://container.thephpleague.com/)
            - Event Factory        - Event Manager (PSR-14)
            - ? Database Factory     - Database Manager

          - Register "User" Global Functions

          - Load Template Engine driver
          - Load Hook Manager

          - Create HTTP Server Request, Response              (PSR-7                Guzzle )
            > Populate Server Request with data

        PRE-PROCESS PHASE:
          - Framework Hooks (onBeforeRequest)
            - Load & Create Hooks one by one
            - Foreach Hook call $hook->run(); if == false, terminate running hooks

        PROCESS PHASE:
          - Execute Global Before Middlewares
          - Match Route
            - Execute Routes Before Middlewares
            - If Cached version
              Y: Serve cached data
              N: Load Contreoller
                 - Call Controller->handle()
                   - Load Model
                   - Process data ...
                   - Load View
            - Execute Routes After Middlewares
          - Execute all After Middlewares

        POST-PROCESS PHASE:
          - Framework Hooks (onAfterRequest)
            - Load & Create Hooks one by one
            - Foreach Hook call $hook->run(); if == false, terminate running hooks

  4.  Send response to Client

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

# Using Templates
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

If the example controller is used it initializes the [Plates](http://platesphp.com/) template engine with necessary basics,
and enables the user to use it very easily:

```php
  # Create pageData array
  $pageData['app'] = $app;
  $pageData['var1'] = "value1";
  $pageData['var2'] = "value2";

  # Render a template
  $html = $engine->render('TheFileName', $pageData );  // loads "/Views/Pages/TheFileName.html"

  # Send response
  response($html);
```

