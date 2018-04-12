<?php declare(strict_types=1);

/**
 * Abstract Route
 *
 * @package  Spin
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\RouteInterface;

abstract class Route extends AbstractBaseClass implements RouteInterface
{
  /** @var      string        HTTP Method */
  protected $method;

  /** @var      string        URI path */
  protected $path;

  /** @var      string        Handler class name */
  protected $handler;

  /**
   * { function_description }
   *
   * @param      string  $method   The method
   * @param      string  $path     The path
   * @param      string  $handler  The handler
   */
  public function __construct(string $method, string $path, string $handler)
  {
    $this->method = $method;
    $this->path = $path;
    $this->handler = $handler;
  }

  /**
   * Gets the method.
   *
   * @return     string  The method.
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * Gets the path.
   *
   * @return     string  The path.
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * Gets the handler.
   *
   * @return     string  The handler.
   */
  public function getHandler()
  {
    return $this->handler;
  }

}
