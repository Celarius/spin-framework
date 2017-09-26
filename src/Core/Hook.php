<?php declare(strict_types=1);

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\HookInterface;

abstract class Hook extends AbstractBaseClass implements HookInterface
{
  protected $name;

  /** Constructor */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Run the hook code
   *
   * @param  array|null $args Arguments to Hook
   *
   * @return void
   */
  abstract public function run(array $args=null);

  /**
   * Get hook name
   *
   * @return   string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Set Hook name
   *
   * @param   string $name [description]
   *
   * @return  self
   */
  public function setName(string $name)
  {
    $this->name = $name;

    return $this;
  }
}
