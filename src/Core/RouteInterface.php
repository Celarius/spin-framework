<?php declare(strict_types=1);

/**
 * RouteInterface
 *
 * @package  Spin
 */

namespace Spin\Core;

class RouteInterface
{
  /**
   * Return Method
   *
   * @return     string
   */
  function getMethod();

  /**
   * Return Path
   *
   * @return     string
   */
  function getPath();

  /**
   * Return Handler
   *
   * @return     string
   */
  function getHandler();
}
