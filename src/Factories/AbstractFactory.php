<?php declare(strict_types=1);

/**
 * AbstractFactory
 *
 * @package  Spin
 */

namespace Spin\Factories;

use \Spin\Factories\AbstractFactoryInterface;
use \Spin\Core\AbstractBaseClass;

abstract class AbstractFactory extends AbstractBaseClass
{
  /** @var  array           Factory Options */
  protected $options;

  /**
   * Factory Constructor
   *
   * @param      array  $options  [description]
   */
  public function __construct(array $options=[])
  {
    parent::__construct();

    $this->options = $options;
  }

  /**
   * Gets the options.
   *
   * @return     mixed
   */
  public function getOptions(): array
  {
      return $this->options;
  }

  /**
   * Sets the options.
   *
   * @param      mixed  $options
   *
   * @return     self
   */
  public function setOptions(array $options)
  {
      $this->options = $options;

      return $this;
  }
}
