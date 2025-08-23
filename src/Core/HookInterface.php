<?php declare(strict_types=1);

/**
 * Hook Interface
 *
 * Defines the contract for lifecycle hook implementations. Specifies methods
 * for executing hook code and managing hook metadata like names and arguments.
 * Implemented by Hook base class to provide framework event hook capabilities.
 *
 * @package  Spin\Core
 * @author   Spin Framework Team
 * @since    1.0.0
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
  public function run(array $args=null);

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
