<?php declare(strict_types=1);

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\RouteInterface;

abstract class Route extends AbstractBaseClass implements RouteInterface
{
  protected $method;
  protected $path;
  protected $handler;

  public function __construct(string $method, string $path, string $handler)
  {
    $this->method = $method;
    $this->path = $path;
    $this->handler = $handler;
  }

  public function getMethod()
  {
    return $this->method;
  }

  public function getPath()
  {
    return $this->path;
  }

  public function getHandler()
  {
    return $this->handler;
  }

}
