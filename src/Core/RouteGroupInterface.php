<?php declare(strict_types=1);

/**
 * RouteGroupInterface
 *
 * @package  Spin
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
