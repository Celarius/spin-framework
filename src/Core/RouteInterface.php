<?php declare(strict_types=1);

namespace Spin\Core;

class RouteInterface
{
  /**
   * Return Method
   *
   * @return string
   */
  public function getMethod();

  /**
   * Return Path
   *
   * @return string
   */
  public function getPath();

  /**
   * Return Handler
   *
   * @return string
   */
  public function getHandler();
}
