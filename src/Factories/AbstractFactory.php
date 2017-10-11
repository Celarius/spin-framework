<?php declare(strict_types=1);

/**
 * Abstract Factory
 */

namespace Spin\Factories;

use \Spin\Core\AbstractBaseClass;

abstract class AbstractFactory extends AbstractBaseClass
{
  /** @var array Factory Options */
  protected $options;

  /**
   * Factory Constructor
   *
   * @param array $options [description]
   */
  public function __construct(array $options=[])
  {
    parent::__construct();

    $this->options = $options;
  }

  /**
   * @return mixed
   */
  public function getOptions()
  {
      return $this->options;
  }

  /**
   * @param mixed $options
   *
   * @return self
   */
  public function setOptions($options)
  {
      $this->options = $options;

      return $this;
  }
}
