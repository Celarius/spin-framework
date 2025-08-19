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

  public function getMethod(): string
  {
    return $this->method;
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function getHandler(): string
  {
    return $this->handler;
  }

}
