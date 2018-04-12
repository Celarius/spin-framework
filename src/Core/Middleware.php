<?php declare(strict_types=1);

/**
 * Abstract Middleware
 *
 * @package  Spin
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
