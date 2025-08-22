<?php declare(strict_types=1);

/**
 * Hook Manager Class
 *
 * Manages the registration, execution, and lifecycle of framework hooks.
 * Provides methods for adding, removing, and retrieving hooks by name,
 * supporting both before and after request hook types.
 *
 * @package  Spin\Core
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\HookManagerInterface;
use \Spin\Core\Hook;

class HookManager extends AbstractBaseClass implements HookManagerInterface
{
  /** List of HOOK constants */
  const ON_BEFORE_REQUEST = 1;
  const ON_AFTER_REQUEST = 2;

  /** @var  array      List of loaded Hook objects */
  protected array $hooks;

  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct();

    # Create list for hook objects
    $this->hooks = [];
  }

  /**
   * Get a Hook Object by $name
   *
   * @param      string  $name   Hook Name
   *
   * @return     Hook
   */
  public function getHook(string $name): Hook
  {
    # Find hook in list
    foreach ($this->hooks as $hook)
    {
      if ( \strcasecmp($name, $hook->getName())==0 ) {
        return $hook;
      }
    }

    # If hook not found, throw an exception
    throw new \RuntimeException("Hook '{$name}' not found");
  }

  /**
   * Add a Hook by $name
   *
   * @param      mixed  $hook
   *
   * @return     self
   */
  public function addHook(Hook $hook)
  {
    # Attempt to find it first
    $exists = $this->getHook($hook->getName());

    # If it exists, return with null
    if (!\is_null($exists)) {
      return $this;
    }

    # Add it to the list
    $this->hooks[$hook->getName()];

    return $this;
  }

  /**
   * Remove a Hook by $name
   *
   * @param      string  $name   Hook Name
   *
   * @return     bool
   */
  public function removeHook(string $name): bool
  {
    foreach ($this->hooks as $idx => $hook)
    {
      if ( \strcasecmp($name, $hook->getName())==0 ) {
        # Remove it
        unset( $this->hooks[$idx] );

        return true;
      }
    }

    return false;
  }

}
