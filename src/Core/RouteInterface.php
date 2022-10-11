<?php declare(strict_types=1);

/**
 * RouteInterface
 *
 * @package  Spin
 */

namespace Spin\Core;

interface RouteInterface
{
  /**
   * Constructor
   *
   * @param   string $method          The method
   * @param   string $path            The path
   * @param   string $handler         The handler
   */
  function __construct(string $method, string $path, string $handler);

  /**
   * Gets the handler method name
   *
   * @return  string  The method.
   */
  function getMethod();

  /**
   * Gets the handler path
   *
   * @return  string
   */
  function getPath();

  /**
   * Gets the handler name (classname)
   *
   * @return  string
   */
  function getHandler();
}
