<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent="" -->

* [1. Using Template Engines](#1-using-template-engines)
* [1.1. Twig](#11-twig)
* [1.2. Plates](#12-plates)

<!-- /MarkdownTOC -->

# 1. Using Template Engines
Below are examples of how to integrate various template engines. The method is simple and builds on abstracting a base controller that initializes the template engine, and all controllers that use that engine would extend the controller.

## 1.1. Twig
[Twig (Symphony)](https://twig.symfony.com/doc/2.x/api.html)

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

## 1.2. Plates
[Plates](http://platesphp.com/)

### 1.2.1. AbstractController
```php
<?php declare(strict_types=1);

/**
 * Abstract Plates Controller
 *
 * Initializes The Leauge Plates Template engine,
 * loading the settings from the config file
 */

namespace App\Controllers;

use \Spin\Core\Controller;

abstract class AbstractPlatesController extends Controller
{
  /** @var Object       The Leauge Template Engine */
  protected $engine;

  /**
   * Initialization method
   *
   * This method is called right after the controller has been created
   * before any route specific Middleware handlers
   *
   * @param  array $args    Path variable arguments as name=value pairs
   */
  public function initialize(array $args)
  {
    parent::initialize($args);

    # Create new Plates instance, default to "/Views" folder
    $this->engine = new \League\Plates\Engine(app()->getAppPath().DIRECTORY_SEPARATOR.'Views');

    # Sets the default file extension (from config)
    $this->engine->setFileExtension(config('templates.extension') ?? 'html');

    # Add other folders (from config)
    $this->engine->addFolder('pages', app()->getAppPath().config('templates.pages'));
    $this->engine->addFolder('errors', app()->getAppPath().config('templates.errors'));

    return ;
  }
}
```

### 1.2.2. Controller
```php
<?php declare(strict_types=1);

namespace App\Controllers;

use \App\Controllers\AbstractPlatesController;

class IndexController extends AbstractPlatesController
{
  public function handleGET(array $args)
  {
    # Model to send to view
    $model = ['title'=>'PageTitle', 'user'=>'SuperUser'];

    # Render view
    $html = $this->engine->render('pages::index', $model);

    # Send the generated html
    return response($html);
  }
}
```
