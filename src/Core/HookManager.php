<?php declare(strict_types=1);

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\HookManagerInterface;
use \Spin\Core\Hook;

class HookManager extends AbstractBaseClass implements HookManagerInterface
{
  /** List of HOOK NAME constants */
  const ON_BEFORE_REQUEST = 1;
  const ON_AFTER_REQUEST = 2;

  /** @var array      List of installed Hook objects */
  protected $hooks;

  /** Constructor */
  public function __construct()
  {
    parent::__construct();

    # Create list for hook objects
    $this->hooks = new \SplObjectStorage();
  }

  /**
   * Get a Hook Object by $name
   *
   * @param  string $name Hook Name
   * @return mixed
   */
  public function getHook(string $name): Hook
  {
    $this->hooks->rewind();

    while($this->hooks->valid()) {
      $hook = $this->hooks->current();

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
   * @return bool
   */
  public function addHook(Hook $hook): bool
  {
    $exists = $this->getHook($hook->getName());

    if (!is_null($exists)) {
      return false;
    }

    $this->hooks->attach($)
  }

}
