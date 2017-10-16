<?php declare(strict_types=1);

namespace Spin\Core;

interface HookManagerInterface
{
  /**
   * Get a Hook Object by $name
   *
   * @param  string $name Hook Name
   * @return mixed
   */
  function getHook(string $name): Hook;

  /**
   * Add a Hook by $name
   *
   * @param  mixed  $hook
   * @return self
   */
  function addHook(Hook $hook);

  /**
   * Remove a Hook by $name
   *
   * @param  string $name Hook Name
   * @return bool
   */
  function removeHook(string $name): bool;
}
