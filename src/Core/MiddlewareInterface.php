<?php declare(strict_types=1);

namespace Spin\Core;

interface MiddlewareInterface
{
  /**
   * Initialization method
   *
   * This method is called right after the Middleware has been created
   * before any of the handle methods get called
   *
   * @param  array $args    Path variable arguments as name=value pairs
   *
   * @return bool                   True=OK, False=Failed to initialize
   */
  function initialize(array $args);

  /**
   * Let the Middleware do it's job
   *
   * @param  array  $args           URI parameters as key=value array
   * @return bool                   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool;
}
