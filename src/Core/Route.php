<?php declare(strict_types=1);

/**
 * Abstract Route
 *
 * @package  Spin
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\RouteInterface;

/**
 * Base route definition capturing HTTP method, path and handler mapping.
 * Concrete implementations can add metadata or behavior around route matching
 * and invocation.
 */
abstract class Route extends AbstractBaseClass implements RouteInterface
{
  /** @var  string        HTTP Method */
  protected string $method;

  /** @var  string        URI path */
  protected string $path;

  /** @var  string        Handler class name */
  protected string $handler;

  public function __construct(string $method, string $path, string $handler)
  {
    $this->method = $method;
    $this->path = $path;
    $this->handler = $handler;
  }

  /**
   * Get HTTP method
   *
   * @return string
   */
  public function getMethod(): string
  {
    return $this->method;
  }

  /**
   * Get URI path
   *
   * @return string
   */
  public function getPath(): string
  {
    return $this->path;
  }

  /**
   * Get handler class name
   *
   * @return string
   */
  public function getHandler(): string
  {
    return $this->handler;
  }
}
