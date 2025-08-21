<?php declare(strict_types=1);

/**
 * Hook Manager Interface
 *
 * Defines the contract for hook management operations including hook registration,
 * retrieval, and lifecycle management. Implemented by HookManager to provide
 * centralized hook administration and execution coordination.
 *
 * @package  Spin\Core
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

interface HookManagerInterface
{
  /**
   * Get a Hook Object by $name
   *
   * @param      string  $name   Hook Name
   *
   * @return     mixed
   */
  function getHook(string $name): Hook;

  /**
   * Add a Hook by $name
   *
   * @param      mixed  $hook
   * 
   * @return     self
   */
  function addHook(Hook $hook);

  /**
   * Remove a Hook by $name
   *
   * @param      string  $name   Hook Name
   *
   * @return     self
   */
  function removeHook(string $name): bool;
}
