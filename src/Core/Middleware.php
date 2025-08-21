<?php declare(strict_types=1);

/**
 * Abstract Middleware Base Class
 *
 * Base abstraction for HTTP middleware components that can be executed
 * before or after request handling. Provides initialization and handling
 * hooks for custom middleware implementations.
 *
 * @package  Spin
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\MiddlewareInterface;

abstract class Middleware extends AbstractBaseClass implements MiddlewareInterface
{
  /**
   * Initialization method
   *
   * This method is called right after the Middleware has been created before
   * any of the handle methods get called
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   True=OK, False=Failed to initialize
   */
  function initialize(array $args): bool
  {
    return true;
  }

  /**
   * Let the Middleware do it's job
   *
   * @param      array  $args   URI parameters as key=value array
   *
   * @return     bool   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    return true;
  }

}
