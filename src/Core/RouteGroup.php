<?php declare(strict_types=1);

/**
 * Route Group Management Class
 *
 * Aggregates a set of related routes with a common path prefix and shared
 * before/after middleware. Provides matching utilities against the grouped
 * FastRoute dispatcher for efficient route resolution.
 *
 * @package  Spin
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\Route;
use \Spin\Core\RouteGroupInterface;

use \FastRoute\RouteCollector;
use \FastRoute\DataGenerator\GroupCountBased;
use \FastRoute\RouteParser\Std;

/**
 * Aggregates a set of related routes with a common path prefix and shared
 * before/after middleware. Provides matching utilities against the grouped
 * FastRoute dispatcher.
 */
class RouteGroup extends AbstractBaseClass implements RouteGroupInterface
{
	/** @var  string                Name of group */
	protected $name;

	/** @var  string                Path prefix */
	protected $prefix;

	/** @var  array                 Array of middleware */
	protected $beforeMiddleware = array();

	/** @var  array                 Array of middleware */
	protected $afterMiddleware = array();

	/** @var  array                 Array of routes */
	protected $routes;

	/** @var  RouteCollector        Collector */
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

			if (isset($route['methods'])) {
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
				$methods = \array_values(\explode(',',$route['methods']));
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
	 * Add a new route to the collector
	 *
	 * @param array  $methods HTTP methods
	 * @param string $path    Route path
	 * @param string $handler Handler class@method
	 *
	 * @return self
	 */
	public function addRoute(array $methods, string $path, string $handler)
	{
		$fullPath = $this->getPrefix().(!empty($path) ? '/'.$path : '');
		if (empty($fullPath)) $fullPath = '/';

		$this->routeCollector->addRoute(
			$methods,
			$fullPath,
			$handler
		);

		return $this;
	}

	/**
	 * Match an incoming request against the group's routes
	 *
	 * @param string $method HTTP method
	 * @param string $uri    Request URI path
	 *
	 * @return array<mixed>
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

			# Nothing found
			case \FastRoute\Dispatcher::NOT_FOUND:
				return [];

			# This should never happen to us ...
			case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				return [];

			# Default we return empty
			default:
				return [];
		}
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}

	/**
	 * @return array
	 */
	public function getBeforeMiddleware(): array
	{
		return $this->beforeMiddleware;
	}

	/**
	 * @return array
	 */
	public function getAfterMiddleware(): array
	{
		return $this->afterMiddleware;
	}

	/**
	 * @return array
	 */
	public function getRoutes(): array
	{
		return $this->routes;
	}
}
