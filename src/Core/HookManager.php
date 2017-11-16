<?php declare(strict_types=1);

namespace Spin\Core;

use Spin\Core\AbstractBaseClass;
use Spin\Core\HookManagerInterface;
use Spin\Core\Hook;

class HookManager extends AbstractBaseClass implements HookManagerInterface
{
  /** List of HOOK constants */
  const ON_BEFORE_REQUEST = 1;
  const ON_AFTER_REQUEST = 2;

  /** @var array      List of loaded Hook objects */
  protected $hooks;

  /** Constructor */
  public function __construct()
  {
    parent::__construct();

    # Create list for hook objects
    $this->hooks = [];
  }

  /**
   * Get a Hook Object by $name
   *
   * @param  string $name Hook Name
   * @return mixed
   */
  public function getHook(string $name): Hook
  {
    foreach ($this->hooks as $hook)
    {
      if (strcasecmp($name,$hook->getName()==0)) {
        return $hook;
      }
    }

    return null;
  }

  /**
   * Add a Hook by $name
   *
   * @param  mixed  $hook
   * @return self
   */
  public function addHook(Hook $hook)
  {
    # Attempt to find it first
    $exists = $this->getHook($hook->getName());

    # If it exists, return with null
    if (!is_null($exists)) {
      return false;
    }

    # Add it to the list
    $this->hooks[$hook];

    return $this;
  }

  /**
   * Remove a Hook by $name
   *
   * @param  string $name Hook Name
   * @return bool
   */
  public function removeHook(string $name): bool
  {
    foreach ($this->hooks as $idx => $hook)
    {
      if (strcasecmp($name,$hook->getName()==0)) {
        # Remove it
        unset( $this->hooks[$idx] );

        return true;
      }
    }

    return false;
  }

}
