<?php declare(strict_types=1);

/**
 * HookInterface
 *
 * @package  Spin
 */

namespace Spin\Core;

interface HookInterface
{
  /**
   * Run the hook code
   *
   * @param      array|null  $args   Arguments to Hook
   *
   * @return     void
   */
  abstract public function run(array $args=null);

  /**
   * Get hook name
   *
   * @return     string
   */
  public function getName(): string;

  /**
   * Set Hook name
   *
   * @param      string  $name   [description]
   *
   * @return     self
   */
  public function setName(string $name);
}
