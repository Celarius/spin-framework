<?php declare(strict_types=1);

/**
 * HTTP Factory
 *
 * Produces HTTP Objects with the Guzzle framework
 *
 * @package  Spin
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
}
