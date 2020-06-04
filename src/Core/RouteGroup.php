<?php declare(strict_types=1);

/**
 * RouteGroup
 *
 * @package  Spin
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\Route;
use \Spin\Core\RouteGroupInterface;

class RouteGroup extends AbstractBaseClass implements RouteGroupInterface
{
  /** @var      string        Name of group */
  protected $name;

  /** @var      string        Path prefix */
  protected $prefix;

  /** @var      array         Array of middleware */
  protected $beforeMiddleware = array();

  /** @var      array         Array of middleware */
  protected $afterMiddleware = array();

  /** @var      array         Array of routes */
  protected $routes;

  /** @var      object        Collector */
  protected $routeCollector = null;

  /**
   * Constructor
   *
   * @param      array  $definition  [description]
   */
  public function __construct(array $definition)
  {
    # Route Group properties
    $this->name             = $definition['name'] ?? '';
    $this->prefix           = $definition['prefix'] ?? '';
    $this->beforeMiddleware = $definition['before'] ?? [];
    $this->routes           = $definition['routes'] ?? [];
    $this->afterMiddleware  = $definition['after'] ?? [];

    # Route Parser, dataGenrator & RouteCollector
    $routeParser = new \FastRoute\RouteParser\Std();
    $dataGenerator = new \FastRoute\DataGenerator\GroupCountBased();
    $this->routeCollector = new \FastRoute\RouteCollector($routeParser,$dataGenerator);

    # Add the Routes
    foreach ($this->routes as $route)
    {
      # Method extraction

      # Default to ALL
      $methods = ['GET','POST','PUT','PATCH','DELETE','HEAD','OPTIONS'];

      if (!isset($route['methods'])) {
        $methods = $route['methods'];
      }

      # Is $methods a String, not '*' and not '' ?
      if (
            \is_string($route['methods']) &&
            \strcasecmp($route['methods'],'*')!=0 &&
            !empty(\trim($route['methods']))
          )
      {
        # Support giving methods as comma separated string
        $methods = \array_values(explode(',',$route['methods']));
        # Trim spaces/specials from values
        $methods = \array_map('trim',$methods);
      } else
      # Is it an array, but NOT emtpy ?
      if (isset($route['methods']) && \is_array($route['methods']) && \count($route['methods'])>0) {
        $methods = $route['methods'];
      }

      if ( isset($route['path']) && isset($route['handler']) ) {
        $this->addRoute($methods,\ltrim($route['path'],'/'),$route['handler']);
      }
    }
  }

  /**
   * Add a new route in the route collector
   *
   * @param      array   $methods  The methods
   * @param      string  $path     [description]
   * @param      string  $handler  [description]
   *
   * @return     self
   */
  public function addRoute(array $methods, string $path, string $handler)
  {
    $this->routeCollector->addRoute(
      $methods,
      $this->getPrefix().(!empty($path) ? '/'.$path : ''),
      $handler
    );

    return $this;
  }


  /**
   * Match the $uri against the stored routes
   *
   * @param      string  $method  The method
   * @param      string  $uri     HTTP Method name
   *                              (GET,POST,PUT,DELETE,HEAD,OPTIONS)
   * @param      string  $uri    [description]
   *
   * @return     array   Array with matching info
   */
  public function matchRoute( string $method, string $uri )
  {
    # Make dispatcher
    $dispatcher = new \FastRoute\Dispatcher\GroupCountBased($this->routeCollector->getData());

    # Dispatch the requested METHOD and URI and see if a route matches
    $routeInfo = $dispatcher->dispatch($method, $uri);

    # Examine $RouteInfo response
    switch ($routeInfo[0])
    {
      # We found a route match
      case \FastRoute\Dispatcher::FOUND:
        # URLDecode each argument
        foreach ($routeInfo[2] as $idx=>$r)
        {
          $routeInfo[2][$idx] = \urldecode($r);
        }

        # Return the Handler + args
        return [
          'method'=>$method,
          'path'=>$uri,
          'handler'=>$routeInfo[1],
          'args'=>$routeInfo[2]
        ];
        break;

      # Nothing found
      case \FastRoute\Dispatcher::NOT_FOUND:
        return [];
        break;

      # This should never happen to us ...
      case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        return [];
        break;

      # Default we return empty
      default:
        return [];
        break;
    }
  }

  /**
   * Get the RouteGroup Name
   *
   * @return     string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Get the RouteGroup Prefix
   *
   * @return     string
   */
  public function getPrefix(): string
  {
    return $this->prefix;
  }

  /**
   * Get the Before Middleware array
   *
   * @return     array
   */
  public function getBeforeMiddleware(): array
  {
    return $this->beforeMiddleware;
  }

  /**
   * Get the After Middleware array
   *
   * @return     array
   */
  public function getAfterMiddleware(): array
  {
    return $this->afterMiddleware;
  }

  /**
   * Get the RouteGroup Routes array
   *
   * @return     array
   */
  public function getRoutes(): array
  {
    return $this->middleware;
  }

}
