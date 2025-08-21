<?php declare(strict_types=1);

/**
 * Route Group Interface
 *
 * Defines the contract for route group implementations. Specifies methods for
 * managing collections of related routes with shared middleware and path prefixes.
 * Implemented by RouteGroup to provide framework route grouping capabilities.
 *
 * @package  Spin\Core
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

interface RouteGroupInterface
{
  /**
   * Add a new route in the group
   *
   * @param      array   $methods  [description]
   * @param      string  $path     [description]
   * @param      string  $handler  [description]
   * 
   * @return     self
   */
  function addRoute(array $methods, string $path, string $handler);

  /**
   * Match the $uri against the stored routes
   *
   * @param      string  $method  The method
   * @param      string  $uri     HTTP Method name
   *                              (GET,POST,PUT,DELETE,HEAD,OPTIONS)
   * @param      string  $uri    [description]
   *
   * @return     array  Array with matching info
   */
  function matchRoute( string $method, string $uri );

  /**
   * Get the RouteGroup Name
   *
   * @return     string
   */
  function getName(): string;

  /**
   * Get the RouteGroup Prefix
   *
   * @return     string
   */
  function getPrefix(): string;

  /**
   * Get the Before Middleware array
   *
   * @return     array
   */
  function getBeforeMiddleware(): array;

  /**
   * Get the After Middleware array
   *
   * @return     array
   */
  function getAfterMiddleware(): array;

  /**
   * Get the RouteGroup Routes array
   *
   * @return     array
   */
  function getRoutes(): array;
}
